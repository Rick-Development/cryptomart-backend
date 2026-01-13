<?php

namespace App\Services;

use App\Models\User;
use App\Models\GraphCustomer;
use App\Models\GraphWallet;
use App\Models\GraphTransaction;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Log;

class GraphService
{
    protected $baseUrl;
    protected $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('graph.base_url');
        $this->secretKey = config('graph.secret_key');
    }

    protected function client()
    {
        return Http::withHeaders([
            'x-api-key' => $this->secretKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->baseUrl($this->baseUrl);
    }

    /**
     * Create a Person (Customer) on Graph
     */
    public function createPerson(User $user, array $kycData)
    {
        try {
            // Map user data to Graph expected format
            // Based on doc: https://usegraph.readme.io/reference/create-person
            $payload = [
                'first_name' => $user->firstname,
                'last_name' => $user->lastname,
                'email' => $user->email,
                'phone_number' => $user->full_mobile ?? $user->mobile,
                'date_of_birth' => $kycData['dob'] ?? null,
                'address' => [
                    'street' => $kycData['address'] ?? null,
                    'city' => $kycData['city'] ?? null,
                    'state' => $kycData['state'] ?? null,
                    'postal_code' => $kycData['zip_code'] ?? null,
                    'country' => 'NG', // Assuming NG for now based on context, or pass dynamically
                ],
                'documents' => [
                    'id_number' => $kycData['id_number'] ?? null,
                    'id_type' => $kycData['id_type'] ?? 'NIN', // BVN, NIN, PASSPORT etc
                     // 'id_image' => ... if required base64 or url
                ]
            ];

            $response = $this->client()->post('/v1/people', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                return GraphCustomer::create([
                    'user_id' => $user->id,
                    'graph_id' => $data['id'],
                    'kyc_status' => $data['kyc_status'] ?? 'pending',
                    'data' => $data,
                ]);
            }

            Log::error("Graph Create Person Failed: " . $response->body());
            throw new Exception("Failed to create customer on Graph: " . $response->json()['message'] ?? $response->reason());

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Create a Request for USD Account (Wallet)
     */
    public function createWallet(User $user, $currency = 'USD')
    {
        $customer = GraphCustomer::where('user_id', $user->id)->first();
        if (!$customer) {
            throw new Exception("User is not a registered Graph customer.");
        }

        try {
            // Doc: https://usegraph.readme.io/reference/create-bank-account or equivalent for wallet
            // Assuming payload based on typical structure
            $payload = [
                'person_id' => $customer->graph_id,
                'currency' => $currency,
            ];

            $response = $this->client()->post('/v1/virtual-accounts', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                return GraphWallet::create([
                    'user_id' => $user->id,
                    'graph_customer_id' => $customer->id,
                    'wallet_id' => $data['id'],
                    'account_number' => $data['account_number'] ?? null,
                    'currency' => $data['currency'] ?? $currency,
                    'balance' => $data['balance'] ?? 0,
                    'status' => $data['status'] ?? 'active',
                    'data' => $data,
                ]);
            }

            Log::error("Graph Create Wallet Failed: " . $response->body());
            throw new Exception("Failed to create wallet on Graph: " . $response->json()['message'] ?? $response->reason());

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Wallet Details
     */
    public function getWallet($walletId)
    {
        try {
            $response = $this->client()->get("/v1/virtual-accounts/{$walletId}");
            
            if ($response->successful()) {
                return $response->json();
            }
            throw new Exception("Failed to fetch wallet");
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Transactions
     */
    public function getTransactions($walletId, $page = 1, $limit = 20)
    {
        try {
            $response = $this->client()->get("/v1/transactions", [
                'virtual_account_id' => $walletId,
                'page' => $page,
                'limit' => $limit
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    // ==================== DEPOSIT METHODS ====================

    /**
     * Create Deposit Address for Crypto
     */
    public function createDepositAddress(User $user, $walletId, $currency = 'USDT')
    {
        try {
            $payload = [
                'virtual_account_id' => $walletId,
                'currency' => $currency,
            ];

            $response = $this->client()->post('/v1/deposit-addresses', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("Failed to create deposit address: " . ($response->json()['message'] ?? $response->reason()));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Deposit History
     */
    public function getDeposits($walletId, $page = 1, $limit = 20)
    {
        try {
            $response = $this->client()->get("/v1/deposits", [
                'virtual_account_id' => $walletId,
                'page' => $page,
                'limit' => $limit
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Mock Deposit (Sandbox Only)
     */
    public function mockDeposit($walletId, $amount, $currency = 'USD')
    {
        try {
            $payload = [
                'virtual_account_id' => $walletId,
                'amount' => $amount,
                'currency' => $currency,
            ];

            $response = $this->client()->post('/v1/deposits/mock', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("Failed to mock deposit: " . ($response->json()['message'] ?? $response->reason()));
        } catch (Exception $e) {
            throw $e;
        }
    }

    // ==================== WITHDRAWAL METHODS ====================

    /**
     * List Supported Banks
     */
    public function listBanks($country = 'NG')
    {
        try {
            $response = $this->client()->get('/v1/banks', [
                'country' => $country
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Resolve/Verify Bank Account
     */
    public function resolveBankAccount($bankCode, $accountNumber)
    {
        try {
            $payload = [
                'bank_code' => $bankCode,
                'account_number' => $accountNumber,
            ];

            $response = $this->client()->post('/v1/banks/resolve', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("Failed to resolve account: " . ($response->json()['message'] ?? $response->reason()));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Payout Destination (Save Beneficiary)
     */
    public function createPayoutDestination(User $user, array $data)
    {
        try {
            $customer = GraphCustomer::where('user_id', $user->id)->first();
            if (!$customer) {
                throw new Exception("User is not a registered Graph customer.");
            }

            $payload = [
                'person_id' => $customer->graph_id,
                'type' => $data['type'] ?? 'bank_account', // bank_account, crypto_address
                'currency' => $data['currency'] ?? 'NGN',
                'details' => $data['details'], // bank_code, account_number, account_name OR crypto address
            ];

            $response = $this->client()->post('/v1/payout-destinations', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("Failed to create payout destination: " . ($response->json()['message'] ?? $response->reason()));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Payout (Withdrawal)
     */
    public function createPayout(User $user, $walletId, array $data)
    {
        try {
            $payload = [
                'virtual_account_id' => $walletId,
                'payout_destination_id' => $data['destination_id'],
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'NGN',
                'reference' => $data['reference'] ?? null,
                'narration' => $data['narration'] ?? 'Withdrawal',
            ];

            $response = $this->client()->post('/v1/payouts', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Track transaction locally
                GraphTransaction::create([
                    'user_id' => $user->id,
                    'graph_wallet_id' => GraphWallet::where('wallet_id', $walletId)->value('id'),
                    'transaction_id' => $responseData['id'],
                    'type' => 'withdrawal',
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'NGN',
                    'status' => $responseData['status'] ?? 'pending',
                    'reference' => $data['reference'],
                    'description' => $data['narration'] ?? 'Withdrawal',
                    'metadata' => $responseData,
                ]);

                return $responseData;
            }

            throw new Exception("Failed to create payout: " . ($response->json()['message'] ?? $response->reason()));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Payout History
     */
    public function getPayouts($walletId, $page = 1, $limit = 20)
    {
        try {
            $response = $this->client()->get('/v1/payouts', [
                'virtual_account_id' => $walletId,
                'page' => $page,
                'limit' => $limit
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Update Wallet Balance from Graph API
     */
    public function updateWalletBalance($walletId)
    {
        try {
            $walletData = $this->getWallet($walletId);
            
            $wallet = GraphWallet::where('wallet_id', $walletId)->first();
            if ($wallet && isset($walletData['balance'])) {
                $wallet->update(['balance' => $walletData['balance']]);
                return $wallet;
            }
            
            return null;
        } catch (Exception $e) {
            Log::error("Failed to update wallet balance: " . $e->getMessage());
            return null;
        }
    }

    // ==================== CONVERSION METHODS ====================

    /**
     * Get Exchange Rate
     */
    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        try {
            $response = $this->client()->get('/v1/rates', [
                'from' => $fromCurrency,
                'to' => $toCurrency
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Convert Currency (e.g., USD to NGN)
     */
    public function convertCurrency(User $user, $walletId, array $data)
    {
        try {
            $payload = [
                'virtual_account_id' => $walletId,
                'from_currency' => $data['from_currency'],
                'to_currency' => $data['to_currency'],
                'amount' => $data['amount'],
            ];

            $response = $this->client()->post('/v1/conversions', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Track conversion locally
                GraphTransaction::create([
                    'user_id' => $user->id,
                    'graph_wallet_id' => GraphWallet::where('wallet_id', $walletId)->value('id'),
                    'transaction_id' => $responseData['id'],
                    'type' => 'conversion',
                    'amount' => $data['amount'],
                    'currency' => $data['from_currency'],
                    'status' => $responseData['status'] ?? 'completed',
                    'reference' => 'CONV_' . time(),
                    'description' => "Converted {$data['amount']} {$data['from_currency']} to {$data['to_currency']}",
                    'metadata' => $responseData,
                ]);

                return $responseData;
            }

            throw new Exception("Failed to convert currency: " . ($response->json()['message'] ?? $response->reason()));
        } catch (Exception $e) {
            throw $e;
        }
    }
}
