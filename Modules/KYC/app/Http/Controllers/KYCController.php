<?php

namespace Modules\KYC\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\KYC\app\Services\YouverifyService;

class KYCController extends Controller
{
    public function __construct(protected YouverifyService $youverifyService)
    {
    }

    /**
     * Start a new KYC verification on Youverify.
     */
    public function initiate(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'date_of_birth' => 'required|date_format:Y-m-d',
            'country' => 'required|string|min:2|max:3',
            'document_type' => 'required|string|max:120',
            'document_number' => 'required|string|max:190',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:190',
            'metadata' => 'nullable|array',
        ]);

        $payload['user_id'] = $request->user()?->getKey();

        $response = $this->youverifyService->startIdentityVerification($payload);

        return response()->json([
            'message' => 'Verification initiated successfully.',
            'reference' => data_get($response, 'data.reference') ?? data_get($response, 'reference'),
            'data' => $response,
        ], 201);
    }

    /**
     * Fetch the status of an existing verification.
     */
    public function status(string $reference): JsonResponse
    {
        $response = $this->youverifyService->fetchVerificationStatus($reference);

        return response()->json([
            'reference' => $reference,
            'data' => $response,
        ]);
    }

    /**
     * Handle asynchronous webhook notifications from Youverify.
     */
    public function webhook(Request $request): JsonResponse
    {
        if (! $this->youverifyService->validateWebhookSignature($request)) {
            abort(403, 'Invalid webhook signature');
        }

        $payload = $request->all();

        Log::info('Youverify webhook received', $payload);

        // @todo Dispatch event / update user KYC status here.

        return response()->json(['received' => true]);
    }
}
