<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\GraphCustomer;
use App\Models\GraphWallet;
use App\Services\GraphService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GraphController extends Controller
{
    protected $graphService;

    public function __construct(GraphService $graphService)
    {
        $this->graphService = $graphService;
    }

    public function createCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dob' => 'required|date',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required|string',
            'id_number' => 'required|string',
            'id_type' => 'required|string', // NIN, BVN, PASSPORT
        ]);

        if ($validator->fails()) {
            return Response::errorResponse($validator->errors()->all());
        }

        try {
            $user = auth()->user();
            if (GraphCustomer::where('user_id', $user->id)->exists()) {
                return Response::errorResponse('Customer already exists');
            }

            $customer = $this->graphService->createPerson($user, $validator->validated());
            return Response::successResponse('Customer created successfully', ['customer' => $customer]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    public function createWallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'required|in:USD,EUR,GBP',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $user = auth()->user();
            
            // Check if wallet already exists for this currency
            $existing = GraphWallet::where('user_id', $user->id)
                ->where('currency', $request->currency)
                ->first();
                
            if ($existing) {
                return Response::successResponse('Wallet already exists', ['wallet' => $existing]);
            }

            $wallet = $this->graphService->createWallet($user, $request->currency);
            return Response::successResponse('Wallet created successfully', ['wallet' => $wallet]);

        } catch (\Exception $e) {
            // Check if user needs to be created first
            if (str_contains($e->getMessage(), 'not a registered Graph customer')) {
                 return Response::errorResponse('Please create a Graph profile/customer first.');
            }
            return Response::errorResponse($e->getMessage());
        }
    }

    public function getWallet()
    {
        try {
            $user = auth()->user();
            $wallets = GraphWallet::where('user_id', $user->id)->get();
            return Response::successResponse('Wallets fetched successfully', ['wallets' => $wallets]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    public function getTransactions(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            // Ensure wallet belongs to user
            $wallet = GraphWallet::where('wallet_id', $request->wallet_id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$wallet) {
                 return Response::errorResponse('Wallet not found');
            }

            $transactions = $this->graphService->getTransactions($request->wallet_id);
            return Response::successResponse('Transactions fetched successfully', ['transactions' => $transactions]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    // ==================== DEPOSIT ENDPOINTS ====================

    public function createDepositAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|string',
            'currency' => 'required|in:USDT,USDC,BTC,ETH',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $user = auth()->user();
            $wallet = GraphWallet::where('wallet_id', $request->wallet_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$wallet) {
                return Response::errorResponse('Wallet not found');
            }

            $address = $this->graphService->createDepositAddress($user, $request->wallet_id, $request->currency);
            return Response::successResponse('Deposit address created successfully', ['address' => $address]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    public function getDeposits(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $wallet = GraphWallet::where('wallet_id', $request->wallet_id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$wallet) {
                return Response::errorResponse('Wallet not found');
            }

            $deposits = $this->graphService->getDeposits($request->wallet_id);
            return Response::successResponse('Deposits fetched successfully', ['deposits' => $deposits]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    public function mockDeposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|in:USD,EUR,GBP',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $wallet = GraphWallet::where('wallet_id', $request->wallet_id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$wallet) {
                return Response::errorResponse('Wallet not found');
            }

            $deposit = $this->graphService->mockDeposit($request->wallet_id, $request->amount, $request->currency);
            
            // Update local wallet balance
            $this->graphService->updateWalletBalance($request->wallet_id);
            
            return Response::successResponse('Deposit simulated successfully', ['deposit' => $deposit]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    // ==================== WITHDRAWAL ENDPOINTS ====================

    public function listBanks()
    {
        try {
            $banks = $this->graphService->listBanks('NG');
            return Response::successResponse('Banks fetched successfully', ['banks' => $banks]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    public function verifyBankAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_code' => 'required|string',
            'account_number' => 'required|string|size:10',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $accountDetails = $this->graphService->resolveBankAccount($request->bank_code, $request->account_number);
            return Response::successResponse('Account verified successfully', ['account' => $accountDetails]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    public function createPayoutDestination(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:bank_account,crypto_address',
            'currency' => 'required|string',
            'details' => 'required|array',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $user = auth()->user();
            $destination = $this->graphService->createPayoutDestination($user, $validator->validated());
            return Response::successResponse('Payout destination created successfully', ['destination' => $destination]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    public function withdrawUSD(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|string',
            'destination_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'narration' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $user = auth()->user();
            $wallet = GraphWallet::where('wallet_id', $request->wallet_id)
                ->where('user_id', $user->id)
                ->where('currency', 'USD')
                ->first();

            if (!$wallet) {
                return Response::errorResponse('USD wallet not found');
            }

            if ($wallet->balance < $request->amount) {
                return Response::errorResponse('Insufficient USD balance');
            }

            $data = [
                'destination_id' => $request->destination_id,
                'amount' => $request->amount,
                'currency' => 'USD',
                'reference' => 'WD_USD_' . time() . '_' . $user->id,
                'narration' => $request->narration ?? 'USD Withdrawal',
            ];

            $payout = $this->graphService->createPayout($user, $request->wallet_id, $data);
            $this->graphService->updateWalletBalance($request->wallet_id);
            
            return Response::successResponse('USD withdrawal initiated successfully', ['payout' => $payout]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    public function convertAndWithdrawNGN(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|string',
            'destination_id' => 'required|string',
            'usd_amount' => 'required|numeric|min:1',
            'narration' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $user = auth()->user();
            $wallet = GraphWallet::where('wallet_id', $request->wallet_id)
                ->where('user_id', $user->id)
                ->where('currency', 'USD')
                ->first();

            if (!$wallet) {
                return Response::errorResponse('USD wallet not found');
            }

            if ($wallet->balance < $request->usd_amount) {
                return Response::errorResponse('Insufficient USD balance');
            }

            // Step 1: Convert USD to NGN
            $conversionData = [
                'from_currency' => 'USD',
                'to_currency' => 'NGN',
                'amount' => $request->usd_amount,
            ];

            $conversion = $this->graphService->convertCurrency($user, $request->wallet_id, $conversionData);
            
            // Step 2: Withdraw NGN to bank account
            $payoutData = [
                'destination_id' => $request->destination_id,
                'amount' => $conversion['to_amount'] ?? 0,
                'currency' => 'NGN',
                'reference' => 'WD_NGN_' . time() . '_' . $user->id,
                'narration' => $request->narration ?? 'NGN Withdrawal (Converted from USD)',
            ];

            $payout = $this->graphService->createPayout($user, $request->wallet_id, $payoutData);
            $this->graphService->updateWalletBalance($request->wallet_id);
            
            return Response::successResponse('Conversion and withdrawal successful', [
                'conversion' => $conversion,
                'payout' => $payout
            ]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    public function getWithdrawals(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $wallet = GraphWallet::where('wallet_id', $request->wallet_id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$wallet) {
                return Response::errorResponse('Wallet not found');
            }

            $withdrawals = $this->graphService->getPayouts($request->wallet_id);
            return Response::successResponse('Withdrawals fetched successfully', ['withdrawals' => $withdrawals]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    public function refreshBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $wallet = GraphWallet::where('wallet_id', $request->wallet_id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$wallet) {
                return Response::errorResponse('Wallet not found');
            }

            $updatedWallet = $this->graphService->updateWalletBalance($request->wallet_id);
            return Response::successResponse('Balance refreshed successfully', ['wallet' => $updatedWallet]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    // ==================== CONVERSION ENDPOINTS ====================

    public function getExchangeRate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from' => 'required|in:USD,NGN,EUR,GBP',
            'to' => 'required|in:USD,NGN,EUR,GBP',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $rate = $this->graphService->getExchangeRate($request->from, $request->to);
            return Response::successResponse('Exchange rate fetched successfully', ['rate' => $rate]);
        } catch (\Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }
}
