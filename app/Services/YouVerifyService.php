<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class YouVerifyService
{
    protected string $baseUrl;
    protected ?string $secretKey;
    protected array $endpoints;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl   = rtrim(config('youverify.base_url'), '/');
        $this->secretKey = config('youverify.secret_key');
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
            ->withToken($this->secretKey)
            ->acceptJson();
    }

    /**
     * Start an ID verification with YouVerify.
     *
     * NOTE: The payload structure MUST match YouVerify's current API spec.
     * This method prepares a reasonable default from your dynamic KYC data,
     * but you should adapt the field mapping after confirming with docs.
     *
     * @param \App\Models\User $user
     * @param array $kycValues  The structured values from your dynamic KYC form.
     * @return array            Standardised result: ['reference' => string, 'status' => string, 'raw' => array]
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function startIdVerification(User $user, array $kycValues): array
    {
        $reference = 'yv-' . $user->id . '-' . now()->timestamp;

        // Map your dynamic KYC fields into YouVerify payload fields.
        // Adjust these keys to your actual SetupKyc field names.
        $payload = [
            'reference' => $reference,
            'country'   => Arr::get($kycValues, 'country_code', 'NG'),
            'id_type'   => Arr::get($kycValues, 'id_type', 'nin'),
            'id_number' => Arr::get($kycValues, 'id_number'),
            'first_name' => Arr::get($kycValues, 'first_name', $user->firstname ?? $user->name ?? null),
            'last_name'  => Arr::get($kycValues, 'last_name', $user->lastname ?? null),
            'date_of_birth' => Arr::get($kycValues, 'date_of_birth'),
            'phone'     => Arr::get($kycValues, 'phone', $user->phone ?? null),
            // Optional images if your KYC form collects them as base64 strings or URLs
            'images'    => [
                'selfie'   => Arr::get($kycValues, 'selfie_image'),
                'id_front' => Arr::get($kycValues, 'id_image_front'),
                'id_back'  => Arr::get($kycValues, 'id_image_back'),
            ],
            'callback_url' => route('webhook.youverify'),
        ];

        $response = $this->client()
            ->post($this->endpoints['start_id_verification'] ?? '/v2/identity/id-verification', $payload)
            ->throw(); // throws RequestException on HTTP error

        $body   = $response->json();
        $status = Arr::get($body, 'data.status', Arr::get($body, 'status', 'pending'));

        return [
            'reference' => $reference,
            'status'    => $status,
            'raw'       => $body,
        ];
    }

    /**
     * Fetch the latest verification status from YouVerify for a given reference.
     *
     * @param string $reference
     * @return array ['status' => string, 'raw' => array]
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getVerificationStatus(string $reference): array
    {
        $path = rtrim($this->endpoints['get_verification_status'] ?? '/v2/identity/verifications', '/')
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

    /**
     * Map a YouVerify status string into your internal GlobalConst status.
     *
     * @param string $providerStatus
     * @return int|null
     */
    public function mapStatusToInternal(string $providerStatus): ?int
    {
        $providerStatus = strtolower($providerStatus);

        // Adjust mapping to match your business logic if needed
        return match ($providerStatus) {
            'approved', 'completed', 'success' => \App\Constants\GlobalConst::APPROVED,
            'pending', 'in_progress'           => \App\Constants\GlobalConst::PENDING,
            'rejected', 'failed'               => \App\Constants\GlobalConst::REJECTED,
            default                            => null,
        };
    }
}



