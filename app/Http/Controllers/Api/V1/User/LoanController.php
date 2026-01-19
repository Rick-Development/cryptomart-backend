<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\Loan;
use App\Models\LoanBorrowRequest;
use App\Models\LoanOffer;
use App\Services\LoanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoanController extends Controller
{
    protected $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }

    /**
     * Create a lending offer
     */
    public function createLendingOffer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asset' => 'required|string|in:USDT,USDC,BTC',
            'amount' => 'required|numeric|min:1',
            'duration_days' => 'required|integer|in:30,60,90,180',
        ]);

        if ($validator->fails()) {
            return Response::errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $result = $this->loanService->createLendingOffer(
                auth()->user(),
                $request->asset,
                $request->amount,
                $request->duration_days
            );

            return Response::successResponse('Lending offer created successfully', $result, 201);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Cancel a pending lending offer
     */
    public function cancelLendingOffer($offerId)
    {
        try {
            $result = $this->loanService->cancelLendingOffer(auth()->user(), $offerId);

            return Response::successResponse('Lending offer cancelled successfully', $result);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Create a borrow request
     */
    public function createBorrowRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asset' => 'required|string|in:USDT,USDC,BTC',
            'amount' => 'required|numeric|min:1',
            'duration_days' => 'required|integer|in:30,60,90,180',
            'collateral_asset' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->loanService->createBorrowRequest(
                auth()->user(),
                $request->asset,
                $request->amount,
                $request->duration_days,
                $request->collateral_asset
            );

            return Response::successResponse($result['message'], $result['data'], 201);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Repay an active loan
     */
    public function repayLoan($loanId)
    {
        try {
            $result = $this->loanService->repayLoan(auth()->user(), $loanId);

            return Response::successResponse('Loan repaid successfully', $result);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Get user's lending offers
     */
    public function getMyLendingOffers()
    {
        $offers = LoanOffer::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return Response::successResponse('Lending offers retrieved successfully', $offers);
    }

    /**
     * Get user's borrow requests
     */
    public function getMyBorrowRequests()
    {
        $requests = LoanBorrowRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return Response::successResponse('Borrow requests retrieved successfully', $requests);
    }

    /**
     * Get user's active loans (as borrower)
     */
    public function getMyLoansAsBorrower()
    {
        $loans = Loan::where('borrower_id', auth()->id())
            ->with(['lender'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Response::successResponse('Loans retrieved successfully', $loans);
    }

    /**
     * Get user's active loans (as lender)
     */
    public function getMyLoansAsLender()
    {
        $loans = Loan::where('lender_id', auth()->id())
            ->with(['borrower'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Response::successResponse('Loans retrieved successfully', $loans);
    }

    /**
     * Get loan details
     */
    public function getLoanDetails($loanId)
    {
        $loan = Loan::with(['borrower', 'lender', 'offer', 'request'])
            ->where(function ($query) {
                $query->where('borrower_id', auth()->id())
                    ->orWhere('lender_id', auth()->id());
            })
            ->findOrFail($loanId);

        return Response::successResponse('Loan details retrieved successfully', $loan);
    }

    /**
     * Calculate collateral requirement
     */
    public function calculateCollateral(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asset' => 'required|string|in:USDT,USDC,BTC',
            'amount' => 'required|numeric|min:1',
            'collateral_asset' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $collateralAmount = $this->loanService->calculateCollateralAmount(
                $request->amount,
                $request->asset,
                $request->collateral_asset
            );

            return Response::successResponse('Collateral calculated successfully', [
                'loan_amount' => $request->amount,
                'loan_asset' => $request->asset,
                'collateral_amount' => $collateralAmount,
                'collateral_asset' => $request->collateral_asset,
                'collateralization_ratio' => '125%',
            ]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }
}
