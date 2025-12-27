<?php

namespace Modules\KYC\app\Services;

use Illuminate\Http\Request;

class YouverifyService
{
    protected array $config;

    public function __construct(protected YouverifyClient $client)
    {
        $this->config = config('kyc.youverify', []);
    }

    public function startIdentityVerification(array $payload): array
    {
        $body = [
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'date_of_birth' => $payload['date_of_birth'],
            'country' => strtoupper($payload['country']),
            'document' => [
                'type' => $payload['document_type'],
                'number' => $payload['document_number'],
            ],
            'contact' => array_filter([
                'email' => $payload['email'] ?? null,
                'phone' => $payload['phone'] ?? null,
            ]),
            'metadata' => array_filter([
                'user_id' => $payload['user_id'] ?? null,
                'additional' => $payload['metadata'] ?? null,
            ]),
            'callback_url' => $payload['callback_url'] ?? $this->callbackUrl(),
        ];

        $endpoint = $this->config['endpoints']['identity'] ?? '/v2/identities/verify';

        return $this->client->post($endpoint, $body);
    }

    public function fetchVerificationStatus(string $reference): array
    {
        $template = $this->config['endpoints']['status'] ?? '/v2/identities/{reference}';
        $endpoint = str_replace('{reference}', $reference, $template);

        return $this->client->get($endpoint);
    }

    public function validateWebhookSignature(Request $request): bool
    {
        $secret = $this->config['webhook_secret'] ?? null;

        if (! $secret) {
            return true;
        }

        $signature = $request->header('x-youverify-signature');

        if (! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    protected function callbackUrl(): string
    {
        $base = config('app.url');

        return rtrim($base, '/').'/api/kyc/webhook';
    }
}

