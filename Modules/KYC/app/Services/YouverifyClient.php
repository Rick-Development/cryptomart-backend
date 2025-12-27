<?php

namespace Modules\KYC\app\Services;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Modules\KYC\app\Exceptions\YouverifyException;
use Psr\Http\Message\ResponseInterface;

class YouverifyClient
{
    protected ClientInterface $http;

    public function __construct(
        ClientInterface $http,
        protected string $baseUrl,
        protected ?string $publicKey,
        protected ?string $secretKey
    ) {
        $this->http = $http;
    }

    public static function make(array $config): self
    {
        $client = new Client([
            'timeout' => $config['timeout'] ?? 15,
            'http_errors' => false,
        ]);

        return new self($client, $config['base_url'], $config['public_key'] ?? null, $config['secret_key'] ?? null);
    }

    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    public function post(string $endpoint, array $payload = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $payload]);
    }

    protected function request(string $method, string $endpoint, array $options = []): array
    {
        $options['headers'] = array_merge($this->defaultHeaders(), $options['headers'] ?? []);

        try {
            $response = $this->http->request($method, $this->baseUrl.$endpoint, $options);
        } catch (GuzzleException $exception) {
            throw new YouverifyException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        return $this->normalizeResponse($response);
    }

    protected function defaultHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($this->secretKey) {
            $headers['Authorization'] = 'Bearer '.$this->secretKey;
        }

        if ($this->publicKey) {
            $headers['x-api-key'] = $this->publicKey;
        }

        return $headers;
    }

    protected function normalizeResponse(ResponseInterface $response): array
    {
        $decoded = json_decode((string) $response->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new YouverifyException('Unable to decode Youverify response: '.json_last_error_msg());
        }

        if ($response->getStatusCode() >= 400) {
            $message = $decoded['message'] ?? 'Youverify request failed';
            throw new YouverifyException($message, $response->getStatusCode());
        }

        return $decoded;
    }
}

