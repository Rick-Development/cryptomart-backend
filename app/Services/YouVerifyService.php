<?php

namespace App\Services;

use App\Models\User;
use App\Models\Admin\BasicSettings;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Exception;

class YouVerifyService
{
    protected string $baseUrl;
    protected ?string $secretKey;
    protected array $endpoints;
    protected int $timeout;

    public function __construct()
    {
        // Try common config patterns, fall back to services config if needed
        $configUrl = config('youverify.base_url', config('services.youverify.base_url', 'https://api.youverify.co/v2/'));
        $configKey = config('youverify.secret_key', config('services.youverify.key'));

        // Check for Admin Override in database
        $basicSettings = BasicSettings::first();
        
        $this->baseUrl   = rtrim($configUrl, '/');
        $this->secretKey = ($basicSettings && $basicSettings->youverify_key) ? $basicSettings->youverify_key : $configKey;
        $this->endpoints = config('youverify.endpoints', []);
        $this->timeout   = (int) config('youverify.timeout', 30);
    }

    /**
    * Build an HTTP client instance with auth headers.
    */
    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders([
                'token' => $this->secretKey,
                'Content-Type' => 'application/json',
            ])
            ->acceptJson();
    }

    /**
     * Initiate a standard vForms identity verification workflow.
     */
    public function initiateWorkflow($payload)
    {
        try {
            // Updated endpoint to common YouVerify vForms pattern
            $response = $this->client()->post('identity/vforms/initiate', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("YouVerify Error: " . $response->status() . " - " . $response->body());
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify Webhook Signature (HMAC SHA256)
     */
    public function verifyWebhookSignature($signature, $payload)
    {
        $secret = $this->secretKey;
        $expected = hash_hmac('sha256', json_encode($payload), $secret);
        return hash_equals($expected, $signature);
    }

    /**
     * Start an ID verification with YouVerify (Legacy/Direct).
     */
    public function startIdVerification(User $user, array $kycValues): array
    {
        $reference = 'yv-' . $user->id . '-' . now()->timestamp;

        $payload = [
            'reference' => $reference,
            'country'   => Arr::get($kycValues, 'country_code', 'NG'),
            'id_type'   => Arr::get($kycValues, 'id_type', 'nin'),
            'id_number' => Arr::get($kycValues, 'id_number'),
            'first_name' => Arr::get($kycValues, 'first_name', $user->firstname ?? $user->name ?? null),
            'last_name'  => Arr::get($kycValues, 'last_name', $user->lastname ?? null),
            'date_of_birth' => Arr::get($kycValues, 'date_of_birth'),
            'phone'     => Arr::get($kycValues, 'phone', $user->phone ?? null),
            'images'    => [
                'selfie'   => Arr::get($kycValues, 'selfie_image'),
                'id_front' => Arr::get($kycValues, 'id_image_front'),
                'id_back'  => Arr::get($kycValues, 'id_image_back'),
            ],
            'callback_url' => route('webhook.youverify'),
        ];

        $response = $this->client()
            ->post($this->endpoints['start_id_verification'] ?? '/ identity/id-verification', $payload)
            ->throw();

        $body   = $response->json();
        $status = Arr::get($body, 'data.status', Arr::get($body, 'status', 'pending'));

        return [
            'reference' => $reference,
            'status'    => $status,
            'raw'       => $body,
        ];
    }

    public function getVerificationStatus(string $reference): array
    {
        $path = rtrim($this->endpoints['get_verification_status'] ?? 'identity/verifications', '/')
            . '/' . $reference;

        $response = $this->client()
            ->get($path)
            ->throw();

        $body   = $response->json();
        $status = Arr::get($body, 'data.status', Arr::get($body, 'status', 'unknown'));

        return [
            'status' => $status,
            'raw'    => $body,
        ];
    }
}
