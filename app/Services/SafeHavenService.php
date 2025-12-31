<?php

namespace App\Services;

use App\Http\Helpers\SafeHeaven\AccountHelper;
use App\Http\Helpers\SafeHeaven\TransferHelper;
use App\Http\Helpers\SafeHeaven\VASHelper;
use App\Models\UserWallet;
use App\Models\VirtualAccounts;
use App\Models\User;
use App\Models\Bank;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;

class SafeHavenService
{
    protected $accountHelper;
    protected $transferHelper;
    protected $vasHelper;

    public function __construct(
        AccountHelper $accountHelper,
        TransferHelper $transferHelper,
        VASHelper $vasHelper
    ) {
        $this->accountHelper = $accountHelper;
        $this->transferHelper = $transferHelper;
        $this->vasHelper = $vasHelper;
    }

    /**
     * Create an individual sub-account for a user.
     */
    public function createSubAccount($user)
    {
        $data = [
            "phoneNumber" => "234" . substr($user->mobile, -10),
            "emailAddress" => $user->email,
            "externalReference" => (string) Str::uuid(),
            "firstName" => $user->firstname,
            "lastName" => $user->lastname,
        ];

        $response = $this->accountHelper->createSubAccountInd($data);

        if (($response['status'] ?? false) !== true && ($response['statusCode'] ?? 0) !== 200) {
             throw new Exception("SafeHaven Sub-Account Creation Failed: " . ($response['description'] ?? 'Unknown error'));
        }

        return $response['data'];
    }

    /**
     * Perform name enquiry for bank transfer.
     */
    public function nameEnquiry(string $bankCode, string $accountNumber)
    {
        $data = [
            "bankCode" => $bankCode,
            "accountNumber" => $accountNumber
        ];

        $response = $this->transferHelper->nameEnquiry($data);

        if (($response['status'] ?? false) !== true && ($response['statusCode'] ?? 0) !== 200) {
            throw new Exception("Name Enquiry Failed: " . ($response['description'] ?? 'Unknown error'));
        }

        return $response['data'];
    }

    /**
     * Process bank transfer.
     */
    /**
     * Handle inbound settlement webhook from SafeHaven.
     */
    public function handleSettlement(array $payload)
    {
        Log::info("SafeHaven Settlement Webhook", ['payload' => $payload]);

        // 1. Validate payload structure
        // SafeHaven typically sends account number and amount in the settlement event
        $accountNumber = $payload['account_number'] ?? ($payload['data']['accountNumber'] ?? null);
        $amount = $payload['amount'] ?? ($payload['data']['amount'] ?? 0);
        $status = $payload['status'] ?? ($payload['data']['status'] ?? null);

        if (!$accountNumber || $amount <= 0) {
            Log::warning("Invalid SafeHaven Settlement Payload", ['payload' => $payload]);
            return false;
        }

        // 2. Find the virtual account record
        $va = VirtualAccounts::where('account_number', $accountNumber)
            ->where('provider', 'safehaven')
            ->first();

        if (!$va) {
            Log::error("SafeHaven Virtual Account not found for number: " . $accountNumber);
            return false;
        }

        $user = $va->user;
        if (!$user) {
            Log::error("User not found for SafeHaven Account: " . $accountNumber);
            return false;
        }

        // 3. Find user's NGN wallet
        $wallet = $user->wallets()->where('currency_code', 'NGN')->first();
        if (!$wallet) {
            Log::error("NGN Wallet not found for User: " . $user->id);
            return false;
        }

        // 4. Credit the wallet
        $reference = "safehaven:settlement:" . ($payload['session_id'] ?? ($payload['data']['sessionId'] ?? Str::uuid()));
        
        try {
            WalletService::credit($wallet->id, (string)$amount, $reference, [
                'provider' => 'safehaven',
                'raw_payload' => $payload
            ]);

            Log::info("SafveHaven Settlement Successful", [
                'user_id' => $user->id,
                'amount' => $amount,
                'reference' => $reference
            ]);

            return true;
        } catch (Exception $e) {
            Log::error("SafeHaven Settlement Processing Failed", [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            throw $e;
        }
    }

    /**
     * Sync banks from SafeHaven to local DB.
     */
    public function syncBanks()
    {
        $response = $this->transferHelper->bankList();

        if (($response['status'] ?? false) !== true && ($response['statusCode'] ?? 0) !== 200) {
            throw new Exception("Failed to fetch bank list from SafeHaven: " . ($response['description'] ?? 'Unknown error'));
        }

        $banks = $response['data'];
        $count = 0;

        foreach ($banks as $bank) {
            Bank::updateOrCreate(
                ['code' => $bank['bankCode']],
                [
                    'name' => $bank['name'] ?? $bank['bankName'] ?? 'Unknown Bank',
                    'slug' => Str::slug($bank['name'] ?? $bank['bankName'] ?? 'unknown'),
                    'logo_image' => $bank['logoImage'] ?? null,
                    'is_active' => true
                ]
            );
            $count++;
        }

        return $count;
    }
}
