<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class BushaService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.busha.base_url', 'https://api.connect.busha.co');
        $this->apiKey = config('services.busha.api_key');
    }

    protected function client()
    {
        // Busha Connect API uses Bearer Token
        return Http::withToken($this->apiKey)->acceptJson();
    }

    /**
     * Create Conversion Quote
     * Use this to get the rate/amount for a Buy/Sell operation.
     */
    public function createQuote($sourceCurrency, $targetCurrency, $amount, $amountType = 'source')
    {
        // amountType: 'source' (I want to spend X) or 'target' (I want to receive X)
        
        $payload = [
            'type' => 'convert',
            'source_currency' => $sourceCurrency,
            'target_currency' => $targetCurrency,
        ];

        if ($amountType === 'source') {
            $payload['source_amount'] = (string)$amount;
        } else {
            $payload['target_amount'] = (string)$amount;
        }

        $response = $this->client()->post("{$this->baseUrl}/v1/quotes", $payload);

        if ($response->failed()) {
            \Illuminate\Support\Facades\Log::error('Busha Quote Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload
            ]);
            throw new Exception($response->json('message') ?? 'Failed to create quote. Check logs.');
        }

        return $response->json();
    }

    /**
     * Execute Quote (Transfer)
     * This finalizes the conversion.
     */
    public function executeQuote($quoteId, $reference)
    {
        // Endpoint: POST /v1/transfers
        $response = $this->client()->post("{$this->baseUrl}/v1/transfers", [
            'quote_id' => $quoteId,
            'reference' => $reference // Optional but good for tracking
        ]);

        if ($response->failed()) {
            throw new Exception($response->json('message') ?? 'Failed to execute transfer');
        }

        return $response->json();
    }
}
