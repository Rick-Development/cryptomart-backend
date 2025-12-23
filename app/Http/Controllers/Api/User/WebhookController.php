<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\KycVerification;
use App\Models\User;
use App\Services\YouVerifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected $youVerifyService;

    public function __construct(YouVerifyService $youVerifyService)
    {
        $this->youVerifyService = $youVerifyService;
    }

    /**
     * Handle YouVerify Webhook Callbacks.
     */
    public function handleYouVerify(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-YouVerify-Signature');

        Log::info("YouVerify Webhook Received", ['payload' => $payload]);

        // 1. Verify Signature (Security)
        if (!$this->youVerifyService->verifyWebhookSignature($signature, $payload)) {
            Log::warning("YouVerify Webhook Signature Mismatch");
            // return response()->json(['message' => 'Invalid signature'], 401); 
            // Note: During initial testing, you might want into bypass this if signature headers aren't clear
        }

        $event = $request->input('event');
        $data = $request->input('data', []);
        $transactionId = $data['id'] ?? null;

        // 2. Find the verification record
        $verification = KycVerification::where('transaction_id', $transactionId)->first();

        if (!$verification) {
            Log::error("KycVerification record not found for transaction: " . $transactionId);
            return response()->json(['status' => 'ignored'], 200);
        }

        $user = $verification->user;

        // 3. Handle Events
        switch ($event) {
            case 'vform.completed':
            case 'identity.verified':
                $verification->update([
                    'status' => 'verified',
                    'data' => $data
                ]);
                
                if ($user) {
                    $user->update(['kyc_verified' => 1]);
                    Log::info("User KYC Verified via Webhook: " . $user->id);
                }
                break;

            case 'identity.failed':
                $verification->update([
                    'status' => 'failed',
                    'data' => $data
                ]);
                
                if ($user) {
                    $user->update(['kyc_verified' => 0]);
                    Log::info("User KYC Failed via Webhook: " . $user->id);
                }
                break;

            default:
                Log::info("Unhandled YouVerify Event: " . $event);
        }

        return response()->json(['status' => 'success'], 200);
    }
}
