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
            'Authorization' => 'Bearer ' . $this->secretKey,
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
            // Map user data to Graph expected format
            // Structure based on provided curl example
            $idType = strtolower($kycData['id_type'] ?? 'passport'); // e.g. passport, nin
            
            // Prepare Documents
            $documents = [
                [
                    'type' => $idType,
                    'url' => 'https://rickdevelopment.com/logo.png',//$kycData['id_image_url'] ?? '',
                    'issue_date' => '2020-01-01', // Default or fetch if available
                    'expiry_date' => '2030-01-01', // Default or fetch if available
                    'id_number' => $kycData['id_number']
                ]
            ];

            // Add Bank Statement if available
            if (!empty($kycData['bank_statement_url'])) {
                $documents[] = [
                    'type' => 'bank_statement',
                    'url' => 'https://rickdevelopment.com/logo.png',// $kycData['bank_statement_url'],
                    'issue_date' => '2020-01-01', // Default
                    'expiry_date' => '2030-01-01', // Default
                ];
            }

            $payload = [
                'id_level' => 'primary',
                'id_type' => $idType,
                'kyc_level' => 'basic',
                'name_first' => $user->firstname,
                'name_last' => $user->lastname,
                'name_other' => '',
                'email' => $user->email,
                'phone' => $user->full_mobile ?? $user->mobile,
                'dob' => $user->dob,
                'id_number' => $user->id_number,// $kycData['id_number'],
                'id_country' => 'NG',
                'bank_id_number' => $user->bvn,// $kycData['bvn'] ?? null, // BVN
                'address' => [
                    'line1' => 'Address',//$kycData['address'] ?? 'Address',
                    'line2' => '',
                    'city' => $user->city,// $kycData['city'] ?? 'Lagos',
                    'state' => $user->state,// $kycData['state'] ?? 'Lagos',
                    'country' => 'NG',
                    'postal_code' => $user->zip_code,// $kycData['zip_code'] ?? '100001',
                ],
                'background_information' => [
                    'employment_status' => 'employed',
                    'occupation' => 'Trader',
                    'primary_purpose' => 'personal',
                    'source_of_funds' => 'business',
                    'expected_monthly_inflow' => 100000
                ],
                'documents' => $documents
            ];

            $response = $this->client()->post('/person', $payload);
            \Log::info("Graph Create Person Response: " . $response->body());

            if ($response->successful()) {
                $responseData = $response->json();
                $personData = $responseData['data'];
                
                return GraphCustomer::create([
                    'user_id' => $user->id,
                    'graph_id' => $personData['id'],
                    'kyc_status' => $personData['kyc_status'] ?? 'pending',
                    'data' => $personData,
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
            // Endpoint updated based on user sample
            $payload = [
                'person_id' => $customer->graph_id,
                'currency' => $currency,
                'autosweep_enabled' => false,
                'whitelist_enabled' => false,
                'label' => "Wallet for " . $user->username ?? $user->email,
            ];

            $response = $this->client()->post('/bank_account', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                $walletData = $responseData['data'];
                
                return GraphWallet::create([
                    'user_id' => $user->id,
                    'graph_customer_id' => $customer->id,
                    'wallet_id' => $walletData['id'],
                    'account_number' => $walletData['account_number'] ?? null,
                    'currency' => $walletData['currency'] ?? $currency,
                    'balance' => $walletData['balance'] ?? 0,
                    'status' => $walletData['status'] ?? 'active',
                    'data' => $walletData,
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
            $response = $this->client()->get("/bank_account/{$walletId}");
            
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
            $response = $this->client()->get("/transaction", [
                'account_id' => $walletId,
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
     * Get Deposit History
     */
    public function getDeposits($walletId, $page = 1, $limit = 20)
    {
        try {
            $response = $this->client()->get("/deposit", [
                'account_id' => $walletId,
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
                'account_id' => $walletId,
                'amount' => (int) ($amount * 10), // Convert to subunits (e.g., dollars to cents)
                // 'currency' => $currency, // Sample doesn't show currency, but usually amount implies it on the account.
                'description' => 'Mock deposit via API',
                'sender_name' => 'Test Sender',
            ];

            $response = $this->client()->post('/deposit/mock', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("Failed to mock deposit: " . ($response->json()['message'] ?? $response->reason()));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * List Supported Banks
     */
    public function listBanks($country = 'NG')
    {
        try {
            $response = $this->client()->get('/bank', [
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

            $response = $this->client()->post('/bank/resolve', $payload);

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

            $response = $this->client()->post('/payout_destination', $payload);

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
                'account_id' => $walletId,
                'destination_id' => $data['destination_id'],
                'amount' => (int) ($data['amount'] * 100), // Convert to subunits
                'currency' => $data['currency'] ?? 'NGN',
                'reference' => $data['reference'] ?? null,
                'narration' => $data['narration'] ?? 'Withdrawal',
            ];

            $response = $this->client()->post('/payout', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Track transaction locally
                GraphTransaction::create([
                    'user_id' => $user->id,
                    'graph_wallet_id' => GraphWallet::where('wallet_id', $walletId)->value('id'),
                    'transaction_id' => $responseData['data']['id'] ?? $responseData['id'],
                    'type' => 'withdrawal',
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'NGN',
                    'status' => $responseData['data']['status'] ?? $responseData['status'] ?? 'pending',
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
            $response = $this->client()->get('/payout', [
                'account_id' => $walletId,
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
            $response = $this->getWallet($walletId);
            $walletData = $response['data'] ?? [];
            
            $wallet = GraphWallet::where('wallet_id', $walletId)->first();
            if ($wallet && isset($walletData['balance'])) {
                $wallet->update(['balance' => $walletData['balance']]);
                return $wallet;
            }
            
            return null;
        } catch (Exception $e) {
            \Log::error("Failed to update wallet balance: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Exchange Rate
     */
    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        try {
            $response = $this->client()->get('/rate', [
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
                'account_id' => $walletId,
                'from' => $data['from_currency'], // Likely 'from' instead of 'from_currency'
                'to' => $data['to_currency'],   // Likely 'to' instead of 'to_currency'
                'amount' => (int) ($data['amount'] * 100), // Convert to subunits
            ];

            $response = $this->client()->post('/conversion', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Track conversion locally
                GraphTransaction::create([
                    'user_id' => $user->id,
                    'graph_wallet_id' => GraphWallet::where('wallet_id', $walletId)->value('id'),
                    'transaction_id' => $responseData['data']['id'] ?? $responseData['id'],
                    'type' => 'conversion',
                    'amount' => $data['amount'],
                    'currency' => $data['from_currency'],
                    'status' => $responseData['data']['status'] ?? $responseData['status'] ?? 'completed',
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
