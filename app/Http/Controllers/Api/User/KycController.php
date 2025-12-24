<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Services\YouVerifyService;
use App\Models\KycVerification;
use App\Models\KycTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class KycController extends Controller
{
    protected $youVerifyService;

    public function __construct(YouVerifyService $youVerifyService)
    {
        $this->youVerifyService = $youVerifyService;
    }

    /**
     * Get all KYC Tiers with requirements and descriptions.
     */
    public function tiers()
    {
        $tiers = KycTier::where('status', true)->orderBy('level', 'asc')->get();
        return Response::success($tiers);
    }

    /**
     * Get the current user's KYC tier.
     */
    public function userTier()
    {
        $user = auth()->user();
        $tier = KycTier::where('level', $user->kyc_tier)->first();
        
        return Response::success([
            'current_level' => $user->kyc_tier,
            'tier' => $tier,
            'kyc_verified' => $user->kyc_verified
        ]);
    }

    /**
     * Tier 1 Initiation: BVN + Selfie
     */
    public function tier1Initiate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bvn' => 'required|string|size:11',
            'selfie' => 'required|string', // Expecting base64 image
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $user = auth()->user();
            if ($user->kyc_tier >= 1) {
                return Response::error(['You are already at Tier 1 or higher']);
            }

            $result = $this->youVerifyService->verifyBvn($request->bvn, $request->selfie);

            if (isset($result['status']) && ($result['status'] === 'success' || $result['status'] === 'pending')) {
                $transactionId = $result['data']['id'] ?? null;
                $reference = 'KYC_T1_' . $user->id . '_' . time();

                KycVerification::create([
                    'user_id' => $user->id,
                    'reference' => $reference,
                    'transaction_id' => $transactionId,
                    'level' => 1,
                    'status' => 'pending',
                    'data' => $result['data'] ?? null
                ]);

                return Response::success([
                    'message' => 'BVN initiation successful. OTP sent to your registered phone number.',
                    'transaction_id' => $transactionId,
                    'reference' => $reference
                ]);
            }

            return Response::error([$result['message'] ?? 'Failed to initiate BVN verification']);

        } catch (\Exception $e) {
            Log::error("Tier 1 Initiation Error: " . $e->getMessage());
            return Response::error(['Failed to initiate Tier 1: ' . $e->getMessage()]);
        }
    }

    /**
     * Tier 1 Verification: OTP
     */
    public function tier1Verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $user = auth()->user();
            $verification = KycVerification::where('transaction_id', $request->transaction_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$verification) {
                return Response::error(['Invalid transaction ID']);
            }

            $result = $this->youVerifyService->submitOtp($request->transaction_id, $request->otp);

            if (isset($result['status']) && $result['status'] === 'success') {
                $user->update([
                    'kyc_tier' => 1,
                    'kyc_verified' => 1
                ]);

                $verification->update([
                    'status' => 'verified',
                    'data' => array_merge((array)$verification->data, ['verification_result' => $result['data'] ?? null])
                ]);

                return Response::success(['message' => 'Tier 1 verification successful!']);
            }

            return Response::error([$result['message'] ?? 'OTP verification failed']);

        } catch (\Exception $e) {
            Log::error("Tier 1 Verification Error: " . $e->getMessage());
            return Response::error(['OTP verification failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Tier 2 Initiation: NIN + DOB
     */
    public function tier2Initiate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nin' => 'required|string',
            'dob' => 'required|date',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $user = auth()->user();
            if ($user->kyc_tier < 1) {
                return Response::error(['Please complete Tier 1 first']);
            }
            if ($user->kyc_tier >= 2) {
                return Response::error(['You are already at Tier 2 or higher']);
            }

            // In some cases, NIN verification might require DOB in payload. 
            // My service method is simple but I can expand it if needed.
            $result = $this->youVerifyService->verifyNin($request->nin);

            if (isset($result['status']) && ($result['status'] === 'success' || $result['status'] === 'pending')) {
                $transactionId = $result['data']['id'] ?? null;
                $reference = 'KYC_T2_' . $user->id . '_' . time();

                KycVerification::create([
                    'user_id' => $user->id,
                    'reference' => $reference,
                    'transaction_id' => $transactionId,
                    'level' => 2,
                    'status' => 'pending',
                    'data' => array_merge(['dob' => $request->dob], (array)($result['data'] ?? []))
                ]);

                return Response::success([
                    'message' => 'NIN initiation successful. OTP sent to your NIN-linked phone number.',
                    'transaction_id' => $transactionId,
                    'reference' => $reference
                ]);
            }

            return Response::error([$result['message'] ?? 'Failed to initiate NIN verification']);

        } catch (\Exception $e) {
            Log::error("Tier 2 Initiation Error: " . $e->getMessage());
            return Response::error(['Failed to initiate Tier 2: ' . $e->getMessage()]);
        }
    }

    /**
     * Tier 2 Verification: OTP
     */
    public function tier2Verify(Request $request)
    {
        // Same logic as tier 1 verify but updates to tier 2
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $user = auth()->user();
            $verification = KycVerification::where('transaction_id', $request->transaction_id)
                ->where('user_id', $user->id)
                ->where('level', 2)
                ->first();

            if (!$verification) {
                return Response::error(['Invalid transaction ID for Tier 2']);
            }

            $result = $this->youVerifyService->submitOtp($request->transaction_id, $request->otp);

            if (isset($result['status']) && $result['status'] === 'success') {
                $user->update(['kyc_tier' => 2]);

                $verification->update([
                    'status' => 'verified',
                    'data' => array_merge((array)$verification->data, ['verification_result' => $result['data'] ?? null])
                ]);

                return Response::success(['message' => 'Tier 2 verification successful!']);
            }

            return Response::error([$result['message'] ?? 'OTP verification failed']);

        } catch (\Exception $e) {
            Log::error("Tier 2 Verification Error: " . $e->getMessage());
            return Response::error(['OTP verification failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Tier 3 Submission: Address Verification
     */
    public function tier3Submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postcode' => 'nullable|string',
            'document' => 'required|string', // Base64 proof of address
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $user = auth()->user();
            if ($user->kyc_tier < 2) {
                return Response::error(['Please complete Tier 2 first']);
            }
            if ($user->kyc_tier >= 3) {
                return Response::error(['You are already at Tier 3']);
            }

            $payload = [
                'firstName' => $user->firstname,
                'lastName' => $user->lastname,
                'address' => [
                    'street' => $request->street,
                    'city' => $request->city,
                    'state' => $request->state,
                    'country' => 'Nigeria'
                ],
                'document' => [
                    'type' => 'UTILITY_BILL',
                    'content' => $request->document
                ]
            ];

            $result = $this->youVerifyService->verifyAddress($payload);

            if (isset($result['status']) && ($result['status'] === 'success' || $result['status'] === 'pending')) {
                $reference = 'KYC_T3_' . $user->id . '_' . time();
                
                KycVerification::create([
                    'user_id' => $user->id,
                    'reference' => $reference,
                    'transaction_id' => $result['data']['id'] ?? null,
                    'level' => 3,
                    'status' => 'pending',
                    'data' => $result['data'] ?? null
                ]);

                return Response::success([
                    'message' => 'Address verification submitted. It will be reviewed shortly.',
                    'reference' => $reference
                ]);
            }

            return Response::error([$result['message'] ?? 'Failed to submit address verification']);

        } catch (\Exception $e) {
            Log::error("Tier 3 Submission Error: " . $e->getMessage());
            return Response::error(['Failed to submit Tier 3: ' . $e->getMessage()]);
        }
    }
}
