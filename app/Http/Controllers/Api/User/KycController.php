<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Services\YouVerifyService;
use App\Models\KycVerification;
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
     * Initiate KYC Verification Workflow using YouVerify vForms.
     */
    public function initiate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email',
            'dob'        => 'required|date',
            'vform_id'   => 'required|string', // The specific workflow ID from YouVerify dashboard
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        try {
            $user = auth()->user();
            $reference = 'KYC_' . $user->id . '_' . time();

            // 1. Prepare payload for YouVerify vForms
            $payload = [
                'vFormId'   => $request->vform_id,
                'firstName' => $request->first_name,
                'lastName'  => $request->last_name,
                'email'     => $request->email,
                'metadata'  => [
                    'user_id' => $user->id,
                    'reference' => $reference
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
            'details' => $verification->data
        ]);
    }
}
