<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanBorrowRequest;
use App\Models\LoanOffer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanService
{
    protected $quidaxService;

    public function __construct()
    {
        // We'll integrate with Quidax for balance checks and transfers
    }

    /**
     * Create a lending offer
     */
    public function createLendingOffer(User $user, string $asset, float $amount, int $durationDays)
    {
        // Check if user has active outstanding loan as borrower
        $hasActiveLoan = Loan::where('borrower_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($hasActiveLoan) {
            throw new \Exception('You cannot lend while you have an active outstanding loan.');
        }

        // Check Quidax balance
        $balance = $this->getQuidaxBalance($user, $asset);
        
        if ($balance < $amount) {
            throw new \Exception('Insufficient ' . $asset . ' balance. Available: ' . $balance);
        }

        // Create lending offer with status 'pending'
        DB::beginTransaction();
        try {
            $offer = LoanOffer::create([
                'user_id' => $user->id,
                'asset' => $asset,
                'amount' => $amount,
                'remaining_amount' => $amount,
                'min_interest_rate' => 5.00, // 5% monthly
                'duration_days' => $durationDays,
                'status' => 'pending',
            ]);

            // Lock funds (logical lock - we track this in our DB)
            // Funds remain in Quidax but are marked as locked
            $this->lockFundsForLending($user, $asset, $amount, $offer->id);

            DB::commit();

            return $offer;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancel a pending lending offer
     */
    public function cancelLendingOffer(User $user, int $offerId)
    {
        $offer = LoanOffer::where('id', $offerId)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Update status
            $offer->update(['status' => 'cancelled']);

            // Unlock funds
            $this->unlockFundsForLending($user, $offer->asset, $offer->remaining_amount, $offer->id);

            DB::commit();

            return $offer;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create a borrow request
     */
    public function createBorrowRequest(User $user, string $asset, float $amount, int $durationDays, string $collateralAsset)
    {
        // Check if user has active outstanding loan
        $hasActiveLoan = Loan::where('borrower_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($hasActiveLoan) {
            throw new \Exception('You already have an active outstanding loan. Please repay it before borrowing again.');
        }

        // Calculate required collateral (120% of loan value)
        $collateralAmount = $this->calculateCollateralAmount($amount, $asset, $collateralAsset);

        // Check collateral balance
        $collateralBalance = $this->getQuidaxBalance($user, $collateralAsset);
        
        if ($collateralBalance < $collateralAmount) {
            throw new \Exception('Insufficient ' . $collateralAsset . ' for collateral. Required: ' . $collateralAmount . ', Available: ' . $collateralBalance);
        }

        DB::beginTransaction();
        try {
            // Create borrow request
            $borrowRequest = LoanBorrowRequest::create([
                'user_id' => $user->id,
                'asset' => $asset,
                'amount' => $amount,
                'duration_days' => $durationDays,
                'collateral_asset' => $collateralAsset,
                'collateral_amount' => $collateralAmount,
                'status' => 'pending',
            ]);

            // Lock collateral
            $this->lockCollateral($user, $collateralAsset, $collateralAmount, $borrowRequest->id);

            // Attempt to match with lending offers
            $matchResult = $this->matchBorrowRequestWithLendingOffer($borrowRequest);

            DB::commit();

            if ($matchResult['matched']) {
                return [
                    'message' => 'Loan disbursement successful',
                    'data' => $matchResult['loan'],
                ];
            } else {
                return [
                    'message' => 'Borrow request created. Waiting for matching lender.',
                    'data' => $borrowRequest,
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Match borrow request with lending offers
     */
    protected function matchBorrowRequestWithLendingOffer(LoanBorrowRequest $borrowRequest)
    {
        // Find matching lending offer
        $matchingOffer = LoanOffer::where('asset', $borrowRequest->asset)
            ->where('duration_days', $borrowRequest->duration_days)
            ->where('remaining_amount', '>=', $borrowRequest->amount)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$matchingOffer) {
            return ['matched' => false];
        }

        DB::beginTransaction();
        try {
            // Calculate interest
            $monthlyRate = 5.00; // 5% monthly
            $months = $borrowRequest->duration_days / 30;
            $totalInterest = $borrowRequest->amount * ($monthlyRate / 100) * $months;

            // Create loan
            $loan = Loan::create([
                'reference_code' => 'LOAN-' . strtoupper(Str::random(10)),
                'borrow_request_id' => $borrowRequest->id,
                'loan_offer_id' => $matchingOffer->id,
                'borrower_id' => $borrowRequest->user_id,
                'lender_id' => $matchingOffer->user_id,
                'asset' => $borrowRequest->asset,
                'amount' => $borrowRequest->amount,
                'collateral_asset' => $borrowRequest->collateral_asset,
                'collateral_amount' => $borrowRequest->collateral_amount,
                'interest_rate' => $monthlyRate,
                'total_interest' => $totalInterest,
                'start_date' => now(),
                'due_date' => now()->addDays($borrowRequest->duration_days),
                'status' => 'active',
            ]);

            // Update statuses
            $borrowRequest->update(['status' => 'matched']);
            
            $matchingOffer->remaining_amount -= $borrowRequest->amount;
            if ($matchingOffer->remaining_amount <= 0) {
                $matchingOffer->status = 'matched';
            }
            $matchingOffer->save();

            // Transfer funds on Quidax (internal transfer from lender to borrower)
            $this->transferFundsOnQuidax(
                $matchingOffer->user,
                $borrowRequest->user,
                $borrowRequest->asset,
                $borrowRequest->amount
            );

            DB::commit();

            return [
                'matched' => true,
                'loan' => $loan->load(['borrower', 'lender']),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Repay a loan
     */
    public function repayLoan(User $user, int $loanId)
    {
        $loan = Loan::where('id', $loanId)
            ->where('borrower_id', $user->id)
            ->where('status', 'active')
            ->firstOrFail();

        $totalRepayment = $loan->amount + $loan->total_interest;

        // Check balance
        $balance = $this->getQuidaxBalance($user, $loan->asset);
        
        if ($balance < $totalRepayment) {
            throw new \Exception('Insufficient ' . $loan->asset . ' balance for repayment. Required: ' . $totalRepayment . ', Available: ' . $balance);
        }

        DB::beginTransaction();
        try {
            // Transfer repayment to lender
            $this->transferFundsOnQuidax(
                $user,
                $loan->lender,
                $loan->asset,
                $totalRepayment
            );

            // Release collateral
            $this->unlockCollateral($user, $loan->collateral_asset, $loan->collateral_amount, $loan->id);

            // Update loan status
            $loan->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            DB::commit();

            return $loan;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate collateral amount (125% of loan value)
     */
    public function calculateCollateralAmount(float $loanAmount, string $loanAsset, string $collateralAsset)
    {
        // Get current prices from Quidax
        $loanAssetPrice = $this->getAssetPrice($loanAsset);
        $collateralAssetPrice = $this->getAssetPrice($collateralAsset);

        // Calculate loan value in USD
        $loanValueUSD = $loanAmount * $loanAssetPrice;

        // Add 25% (125% collateralization)
        $requiredCollateralValueUSD = $loanValueUSD * 1.25;

        // Convert to collateral asset amount
        $collateralAmount = $requiredCollateralValueUSD / $collateralAssetPrice;

        return round($collateralAmount, 8);
    }

    /**
     * Get Quidax balance for a specific asset
     */
    protected function getQuidaxBalance(User $user, string $asset)
    {
        // TODO: Integrate with actual Quidax API
        // For now, return a mock value
        return 1000.00;
    }

    /**
     * Get asset price from Quidax
     */
    protected function getAssetPrice(string $asset)
    {
        // TODO: Integrate with actual Quidax API for real-time prices
        // Mock prices for now
        $prices = [
            'USDT' => 1.00,
            'USDC' => 1.00,
            'BTC' => 45000.00,
            'SOL' => 100.00,
            'BNB' => 300.00,
        ];

        return $prices[$asset] ?? 1.00;
    }

    /**
     * Lock funds for lending (logical lock in DB)
     */
    protected function lockFundsForLending(User $user, string $asset, float $amount, int $offerId)
    {
        // TODO: Create a locked_funds record or use existing LockedFund model
        // This prevents the user from withdrawing/transferring these funds
    }

    /**
     * Unlock funds for lending
     */
    protected function unlockFundsForLending(User $user, string $asset, float $amount, int $offerId)
    {
        // TODO: Remove locked_funds record
    }

    /**
     * Lock collateral
     */
    protected function lockCollateral(User $user, string $asset, float $amount, int $borrowRequestId)
    {
        // TODO: Create a locked_funds record for collateral
    }

    /**
     * Unlock collateral
     */
    protected function unlockCollateral(User $user, string $asset, float $amount, int $loanId)
    {
        // TODO: Remove locked_funds record for collateral
    }

    /**
     * Transfer funds on Quidax (internal transfer)
     */
    protected function transferFundsOnQuidax(User $from, User $to, string $asset, float $amount)
    {
        // TODO: Integrate with Quidax internal transfer API
        // This should be an internal transfer within Quidax custody
        \Log::info("Quidax Transfer: {$amount} {$asset} from User {$from->id} to User {$to->id}");
    }
}
