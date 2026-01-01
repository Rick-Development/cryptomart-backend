<?php

namespace App\Services;

use App\Http\Helpers\SafeHeaven\AccountHelper;
use App\Http\Helpers\SafeHeaven\TransferHelper;
use App\Http\Helpers\SafeHeaven\VASHelper;
use App\Models\UserWallet;
use App\Models\VirtualAccounts;
use App\Models\User;
use App\Models\Bank;
use App\Models\KycVerification;
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
        // Fetch BVN from KYC Tier 1
        $kyc = KycVerification::where('user_id', $user->id)
            ->where('level', 1)
            ->where('status', 'verified')
            ->first();

        if (!$kyc || !isset($kyc->data['bvn'])) {
            throw new Exception("BVN not found in KYC data. Please complete Tier 1 verification again.");
        }

        $verificationResult = $kyc->data['verification_result'] ?? [];
        
        
        $data = [
            "phoneNumber" => "+234" . substr(preg_replace('/\D/', '', $user->mobile), -10),
            "emailAddress" => $user->email,
            "externalReference" => (string) Str::uuid(),
            "firstName" => $verificationResult['firstName'] ?? $user->firstname,
            "lastName" => $verificationResult['lastName'] ?? $user->lastname,
            "identityType" => "BVN",
            "identityId" => $kyc->transaction_id,
            "identityNumber" => $kyc->data['bvn'] ?? $bvn,
            "autoSweep" => false, 
        ];

        if (!empty($verificationResult['middleName'])) {
            $data['middleName'] = $verificationResult['middleName'];
        }

        // Ensure gender is passed if available
        if (isset($verificationResult['gender'])) {
             $data['gender'] = $verificationResult['gender'];
        }

        // Include DOB if available
        if (isset($verificationResult['dob'])) {
            $data['dateOfBirth'] = $verificationResult['dob'];
        }

        // OTP is CRITICAL for SafeHaven sub-account creation via BVN/NIN
        // However, if we have already verified the OTP in Tier 1, reusing it throws "OTP already verified".
        // But sending no OTP throws "400".
        // Strategy: Try using 'vID' (Verified ID) as identityType if we have a verified identityId.
        
        $data['identityType'] = 'vID';
        // Some docs suggest identityNumber might still be needed or ignored, but safe to send?
        // Let's keep identityNumber for vID just in case, but remove OTP.
        $data['identityNumber'] = $kyc->data['bvn'] ?? $bvn;
        unset($data['otp']); 
        
        // if (isset($kyc->data['otp'])) {
        //    $data['otp'] = $kyc->data['otp'];
        // }

        Log::info("SafeHaven Sub-Account Final Payload", ['payload' => $data]);

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
    public function transfer(array $payload)
    {
        $response = $this->transferHelper->transfer($payload);

        if (($response['status'] ?? false) !== true && ($response['statusCode'] ?? 0) !== 200) {
            throw new Exception("Transfer Failed: " . ($response['message'] ?? $response['description'] ?? 'Unknown error'));
        }

        return $response['data'];
    }

    /**
     * Handle inbound settlement webhook from SafeHaven.
     */
    public function handleSettlement(array $payload)
    {
        Log::info("SafeHaven Settlement Webhook", ['payload' => $payload]);

        // 1. Validate payload structure
        // SafeHaven typically sends account number and amount in the settlement event
        // Check for flat structure (as seen in logs) or nested 'data' (common pattern)
        $accountNumber = $payload['creditAccountNumber'] ?? ($payload['data']['creditAccountNumber'] ?? null);
        
        // Fallback to generic account number if credit specific one is missing
        if (!$accountNumber) {
            $accountNumber = $payload['accountNumber'] ?? ($payload['data']['accountNumber'] ?? null);
        }

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
        
        // Calculate Amount to Credit
        // SafeHaven fee is "fees" in the payload. We must deduct this so the platform doesn't lose money.
        $providerFee = $payload['fees'] ?? ($payload['data']['fees'] ?? 0);
        $creditAmount = $amount - $providerFee;

        if ($creditAmount <= 0) {
            Log::warning("SafeHaven Settlement Skipped: Fee ($providerFee) exceeds or equals amount ($amount).", ['reference' => $reference]);
            return false;
        }

        try {
            WalletService::credit($wallet->id, (string)$creditAmount, $reference, [
                'provider' => 'safehaven',
                'raw_info' => [
                    'amount_sent' => $amount,
                    'provider_fee' => $providerFee,
                    'account_number' => $accountNumber
                ]
            ]);

            Log::info("SafeHaven Settlement Successful", [
                'user_id' => $user->id,
                'credit_amount' => $creditAmount,
                'fees_deducted' => $providerFee,
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
