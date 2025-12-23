<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Services\YouVerifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KycController extends Controller
{
    protected $youVerifyService;

    public function __construct(YouVerifyService $youVerifyService)
    {
        $this->youVerifyService = $youVerifyService;
    }

    /**
     * Initiate KYC Verification Workflow.
     * Returns payload for Mobile SDK initialization.
     */
    public function initiate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email',
            'dob'        => 'required|date',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors());
        }

        try {
            $user = auth()->user();
            
            // Prepare payload for YouVerify SDK/Workflow
            $payload = [
                'firstName' => $request->first_name,
                'lastName'  => $request->last_name,
                'email'     => $request->email, // Ensure email matches user account
                'dob'       => $request->dob,
                'metadata'  => [
                    'user_id' => $user->id,
                    'ref'     => 'KYC_' . $user->id . '_' . time() 
                ]
            ];

            // In a real integration, this might return a transaction reference or SDK token
            $sdkPayload = $this->youVerifyService->initialteWorkflow($payload);

            return Response::success([
                'message' => 'KYC initiated successfully',
                'sdk_payload' => $sdkPayload,
                'reference' => $payload['metadata']['ref']
            ]);

        } catch (\Exception $e) {
            return Response::error(['error' => 'Failed to initiate KYC: ' . $e->getMessage()]);
        }
    }

    /**
     * Webhook or Callback to update status (optional if handled via WebhookController)
     */
    public function status(Request $request) {
       // Logic to check status manually if needed
    }
}
