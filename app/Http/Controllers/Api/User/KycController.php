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
     * Initiate KYC Verification Workflow using YouVerify vForms.
     */
    public function initiate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email',
            'dob'        => 'required|date',
            'level'      => 'required|integer|exists:kyc_tiers,level', 
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $user = auth()->user();
            $level = $request->level;

            // Check if user is already at this level or higher
            if ($user->kyc_tier >= $level) {
                return Response::error(["You are already at or above Level $level"]);
            }

            // Check if user has completed the previous level
            if ($level > 1 && $user->kyc_tier < ($level - 1)) {
                return Response::error(["Please complete Level " . ($level - 1) . " first"]);
            }

            $tier = KycTier::where('level', $level)->first();
            if (!$tier || !$tier->vform_id) {
                return Response::error(["Level $level is not properly configured"]);
            }

            $reference = 'KYC_L' . $level . '_' . $user->id . '_' . time();

            // 1. Prepare payload for YouVerify vForms
            $payload = [
                'vFormId'   => $tier->vform_id,
                'firstName' => $request->first_name,
                'lastName'  => $request->last_name,
                'email'     => $request->email,
                'metadata'  => [
                    'user_id' => $user->id,
                    'reference' => $reference,
                    'level' => $level
                ]
            ];

            // 2. Call YouVerify
            $result = $this->youVerifyService->initiateWorkflow($payload);

            if (isset($result['status']) && $result['status'] === 'success') {
                $data = $result['data'] ?? [];
                
                // 3. Log verification attempt
                KycVerification::create([
                    'user_id' => $user->id,
                    'reference' => $reference,
                    'transaction_id' => $data['id'] ?? null,
                    'level' => $level,
                    'status' => 'pending',
                ]);

                return Response::success([
                    'message' => 'KYC initiated successfully',
                    'url' => $data['url'] ?? null,
                    'reference' => $reference
                ]);
            }

            return Response::error(['Failed to initiate KYC with YouVerify']);

        } catch (\Exception $e) {
            Log::error("KYC Initiation Error: " . $e->getMessage());
            return Response::error(['error' => 'Failed to initiate KYC: ' . $e->getMessage()]);
        }
    }

    /**
     * Poll status of a specific KYC verification if needed.
     */
    public function status($reference)
    {
        $verification = KycVerification::where('reference', $reference)
            ->where('user_id', auth()->id())
            ->first();

        if (!$verification) {
            return Response::error(['Verification not found']);
        }

        return Response::success([
            'status' => $verification->status,
            'level' => $verification->level,
            'details' => $verification->data
        ]);
    }
}
