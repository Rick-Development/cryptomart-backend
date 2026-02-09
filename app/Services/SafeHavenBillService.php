<?php

namespace App\Services;

use App\Http\Helpers\SafeHeaven\VASHelper;
use App\Services\WalletService;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SafeHavenBillService
{
    protected $vasHelper;

    public function __construct(VASHelper $vasHelper)
    {
        $this->vasHelper = $vasHelper;
    }

    /**
     * Get All Services from Database
     */
    public function getServices()
    {
        $services = \App\Models\VasService::all();
        
        return [
            'statusCode' => 200,
            'message' => 'Services fetched successfully',
            'data' => $services->map(function($service) {
                return [
                    '_id' => $service->safehaven_id,
                    'name' => $service->name,
                    'identifier' => $service->identifier,
                    'description' => $service->description,
                    'createdAt' => $service->created_at,
                    'updatedAt' => $service->updated_at,
                ];
            })
        ];
    }

    /**
     * Get Categories for a Service (e.g., MTN, DSTV)
     */
    public function getCategories(string $serviceId)
    {
        $response = $this->vasHelper->serviceCategories($serviceId);
        return $this->handleResponse($response);
    }

    /**
     * Get Products for a Category (e.g., specific plans)
     */
    public function getProducts(string $categoryId)
    {
        $response = $this->vasHelper->serviceProducts($categoryId);
        return $this->handleResponse($response);
    }

    /**
     * Verify Customer (Meter No / Smartcard No)
     */
    public function verifyCustomer(array $data)
    {
        // $data should contain 'serviceCategoryId' and 'entityNumber'
        $response = $this->vasHelper->verifyProduct($data);
        return $this->handleResponse($response);
    }

    /**
     * Purchase Bill
     * Handles local debit and API call with atomicity.
     * 
     * @param \App\Models\User $user
     * @param string $type (airtime, data, cable, utility)
     * @param array $data API Payload
     */
    public function purchase($user, string $type, array $data)
    {
        // Map user-friendly fields to SafeHaven API requirements
        $data = $this->mapFieldsToApi($type, $data);
        
        $amount = $data['amount'] ?? 0;
        
        // 1. Validation
        $wallet = $user->wallets()->where('currency_code', 'NGN')->first();
        if (!$wallet) {
            throw new Exception("NGN Wallet not found.");
        }

        if (bccomp($wallet->balance, $amount, 8) < 0) {
            throw new Exception("Insufficient wallet balance.");
        }

        // Get User's SafeHaven Sub-Account
        $subAccount = $user->virtualAccounts()->where('provider', 'safehaven')->value('account_number');
        if (!$subAccount) {
            throw new Exception("You do not have a SafeHaven sub-account to fund this transaction. Please create one first.");
        }

        // Prepare Payload
        $data['debitAccountNumber'] = $subAccount;
        $data['channel'] = $data['channel'] ?? 'WEB'; // Default channel
        
        // Generate Reference for Local and API
        $reference = 'BILL-' . strtoupper(Str::random(12));
        
        return DB::transaction(function () use ($user, $wallet, $amount, $type, $data, $reference) {
            // 2. Debit Local Wallet
            WalletService::debit($wallet->id, (string)$amount, $reference, [
                'type' => 'bill_payment',
                'bill_type' => $type,
                'provider' => 'safehaven',
                'details' => $data
            ]);

            try {
                // 3. Call SafeHaven API
                $response = [];
                switch ($type) {
                    case 'airtime':
                        $response = $this->vasHelper->airtime($data);
                        break;
                    case 'data':
                        $response = $this->vasHelper->data($data);
                        break;
                    case 'cable':
                        $response = $this->vasHelper->cableTv($data);
                        break;
                    case 'utility':
                        $response = $this->vasHelper->utilityBills($data);
                        break;
                    default:
                        throw new Exception("Invalid bill payment type.");
                }

                $result = $this->handleResponse($response);

                Log::info("SafeHaven Bill Purchase Success", ['user_id' => $user->id, 'type' => $type, 'result' => $result]);
                
                return $result;

            } catch (Exception $e) {
                // 4. Refund Logic on Failure
                Log::error("SafeHaven Bill Purchase Failed - Refunding", ['error' => $e->getMessage(), 'user_id' => $user->id]);
                
                WalletService::credit($wallet->id, (string)$amount, $reference . '-REFUND', [
                    'reason' => 'Bill Payment Failed: ' . $e->getMessage(),
                    'original_ref' => $reference
                ]);
                
                throw $e;
            }
        });
    }

    /**
     * Map user-friendly fields to SafeHaven API requirements
     */
    protected function mapFieldsToApi(string $type, array $data)
    {
        // This mapping would ideally come from database, but hardcoding for now
        // You should fetch actual category IDs from SafeHaven API and store in DB
        
        switch ($type) {
            case 'airtime':
                $networkMap = [
                    'MTN' => 'MTN_AIRTIME_CATEGORY_ID',
                    'GLO' => 'GLO_AIRTIME_CATEGORY_ID',
                    'AIRTEL' => 'AIRTEL_AIRTIME_CATEGORY_ID',
                    '9MOBILE' => '9MOBILE_AIRTIME_CATEGORY_ID',
                ];
                $data['serviceCategoryId'] = $networkMap[$data['network'] ?? ''] ?? null;
                break;
                
            case 'data':
                $networkMap = [
                    'MTN' => 'MTN_DATA_CATEGORY_ID',
                    'GLO' => 'GLO_DATA_CATEGORY_ID',
                    'AIRTEL' => 'AIRTEL_DATA_CATEGORY_ID',
                    '9MOBILE' => '9MOBILE_DATA_CATEGORY_ID',
                ];
                $data['serviceCategoryId'] = $networkMap[$data['network'] ?? ''] ?? null;
                break;
                
            case 'cable':
                $providerMap = [
                    'DSTV' => 'DSTV_CATEGORY_ID',
                    'GOTV' => 'GOTV_CATEGORY_ID',
                    'STARTIMES' => 'STARTIMES_CATEGORY_ID',
                ];
                $data['serviceCategoryId'] = $providerMap[$data['provider'] ?? ''] ?? null;
                break;
                
            case 'utility':
                $providerMap = [
                    'EKEDC' => 'EKEDC_CATEGORY_ID',
                    'IKEDC' => 'IKEDC_CATEGORY_ID',
                    'AEDC' => 'AEDC_CATEGORY_ID',
                    'PHED' => 'PHED_CATEGORY_ID',
                    'KEDCO' => 'KEDCO_CATEGORY_ID',
                ];
                $data['serviceCategoryId'] = $providerMap[$data['provider'] ?? ''] ?? null;
                break;
        }
        
        if (empty($data['serviceCategoryId'])) {
            throw new Exception("Invalid network/provider specified.");
        }
        
        return $data;
    }

    protected function handleResponse($response)
    {
        if (!is_array($response)) {
             throw new Exception("Invalid response from SafeHaven Service.");
        }

        if (($response['statusCode'] ?? 0) !== 200) {
            throw new Exception($response['message'] ?? $response['description'] ?? 'SafeHaven Service Error');
        }

        return $response['data'] ?? [];
    }
}
