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
                    'url' => $kycData['id_image_url'] ?? '',
                    'issue_date' => '2020-01-01', // Default or fetch if available
                    'expiry_date' => '2030-01-01', // Default or fetch if available
                    'id_number' => $kycData['id_number']
                ]
            ];

            // Add Bank Statement if available
            if (!empty($kycData['bank_statement_url'])) {
                $documents[] = [
                    'type' => 'bank_statement',
                    'url' => $kycData['bank_statement_url'],
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
                'dob' => $kycData['dob'],
                'id_number' => $kycData['id_number'],
                'id_country' => 'NG',
                'bank_id_number' => $kycData['bvn'] ?? null, // BVN
                'address' => [
                    'line1' => $kycData['address'] ?? 'Address',
                    'line2' => '',
                    'city' => $kycData['city'] ?? 'Lagos',
                    'state' => $kycData['state'] ?? 'Lagos',
                    'country' => 'NG',
                    'postal_code' => $kycData['zip_code'] ?? '100001',
                ],
                'background_information' => [
                    'employment_status' => $kycData['background_information']['employment_status'] ?? 'employed',
                    'occupation' => $kycData['background_information']['occupation'] ?? 'Trader',
                    'primary_purpose' => $kycData['background_information']['primary_purpose'] ?? 'personal',
                    'source_of_funds' => $kycData['background_information']['source_of_funds'] ?? 'business',
                    'expected_monthly_inflow' => (int) ($kycData['background_information']['expected_monthly_inflow'] ?? 100000)
                ],
                'documents' => $documents
            ];

            \Log::info('Graph Create Person Payload:', $payload);
            
            $response = $this->client()->post('/person', $payload);
            \Log::info("Graph Create Person Response: " . $response->body());

            if ($response->successful()) {
                $responseData = $response->json();
                $personData = $responseData['data'];
                
                return GraphCustomer::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'graph_id' => $personData['id'],
                        'kyc_status' => $personData['kyc_status'] ?? 'pending',
                        'data' => $personData,
                    ]
                );
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
     * Create Deposit Address for Crypto
     */
    public function createDepositAddress(User $user, $walletId, $currency = 'USDT', $network = 'ERC20')
    {
        try {
            $payload = [
                // 'virtual_account_id' => $walletId, // Removed as per sample curl which doesn't use it
                'currency' => $currency,
                'network' => $network,
                'label' => "Deposit for " . ($user->username ?? $user->email),
            ];

            $response = $this->client()->post('/address', $payload);

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
    /**
     * Create Payout Destination (Save Beneficiary)
     */
    public function createPayoutDestination(User $user, array $data)
    {
        try {
            // Determine Graph API 'type' based on input
            $inputType = $data['type'] ?? 'bank_account'; // bank_account, crypto_address
            $currency = $data['currency'] ?? 'NGN';
            
            $graphType = 'nip'; // Default
            if ($inputType == 'bank_account') {
                if ($currency == 'NGN') {
                    $graphType = 'nip';
                } elseif ($currency == 'USD') {
                    $graphType = 'wire';
                }
            } elseif ($inputType == 'crypto_address') {
                $graphType = 'stablecoin';
            }

            $details = $data['details'] ?? [];
            
            // Base Payload
            $payload = [
                'type' => $graphType,
                'label' => $data['label'] ?? ('Beneficiary for ' . $user->username),
                'source_type' => 'wallet_account', // Default
            ];

            // Add fields based on type
            if ($graphType == 'nip') {
                $payload = array_merge($payload, [
                    'account_number' => $details['account_number'] ?? null,
                    'bank_code' => $details['bank_code'] ?? null,
                    'beneficiary_name' => $details['account_name'] ?? 'Beneficiary',
                    'currency' => 'NGN',
                ]);
            } elseif ($graphType == 'wire') {
                $payload = array_merge($payload, [
                    'account_number' => $details['account_number'] ?? null,
                    'beneficiary_name' => $details['account_name'] ?? 'Beneficiary',
                    'currency' => 'USD',
                    'wire_type' => $details['wire_type'] ?? 'ach', // ach, fedwire, swift
                    'routing_number' => $details['routing_number'] ?? null,
                    'swift_code' => $details['swift_code'] ?? null,
                    'bank_name' => $details['bank_name'] ?? null,
                    'beneficiary_address' => $details['beneficiary_address'] ?? null, // Required object
                    'bank_address' => $details['bank_address'] ?? null, // Required for swift
                    'account_type' => $details['account_type'] ?? 'personal', // personal, business
                ]);
            } elseif ($graphType == 'stablecoin') {
                 $payload = array_merge($payload, [
                    'address_code' => $details['address_code'] ?? $details['address'] ?? null, // Wallet Address
                    'address_network' => $details['network'] ?? 'ERC20',
                    'currency' => $currency, // USDC, USDT
                 ]);
            }

            // account_id (Requ. ired by API). 
            // Ideally should be passed in $data['wallet_id']. 
            // If not, try to find a user wallet or fail.
            if (isset($data['wallet_id'])) {
                $payload['account_id'] = $data['wallet_id'];
            } else {
                 // Try to fetch first user wallet
                 $wallet = GraphWallet::where('user_id', $user->id)->first();
                 if ($wallet) {
                     $payload['account_id'] = $wallet->wallet_id;
                 } else {
                     throw new Exception("Wallet ID (account_id) is required to create a payout destination.");
                 }
            }

            //\Log::info("Graph Create Payout Destination Payload: ", $payload);

            $response = $this->client()->post('/payout-destination', $payload);

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
                'account_id' => $walletId, // Source
                'payout_destination_id' => $data['destination_id'], // Beneficiary
                'amount' => (int) ($data['amount'] * 100), // Convert to subunits
                'currency' => $data['currency'] ?? 'NGN',
                'reference' => $data['reference'] ?? null,
                'narration' => $data['narration'] ?? 'Withdrawal',
            ];

            if (!empty($data['type'])) {
                $payload['type'] = $data['type'];
            }


            //\Log::info("Graph Create Payout Payload: ", $payload);

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
            //\Log::error("Failed to update wallet balance: " . $e->getMessage());
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
