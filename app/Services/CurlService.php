<?php

namespace App\Services;

class CurlService
{
    protected $baseUrl;
    protected $rampUrl;
    protected $headers;

    public function __construct($baseUrl, $token = null, $rampUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->rampUrl = rtrim($rampUrl, '/');
        $this->headers = [
            "accept: application/json",
            "x-private-key: J3Jz4fFfJgRe8xa1RDTCs06PhBbxzV3SGEN9g6SB",
        ];

        if ($token) {
            $this->headers[] = "Authorization: Bearer {$token}";
        }
    }

    // protected function buildUrl($endpoint)
    // {
    //     // Define which endpoints use ramp
    //     $rampEndpoints = ['ramp/', 'ramp-auth/', 'ramp-orders/'];

    //     foreach ($rampEndpoints as $prefix) {
    //         if (str_contains($endpoint, $prefix)) {
    //             return $this->rampUrl . "/{$endpoint}";
    //         }
    //     }

    //     return $this->baseUrl . "/{$endpoint}";
    // }

    protected function request($method, $endpoint, $data = [])
    {
        $curl = curl_init();
        \Log::info("{$this->baseUrl}/{$endpoint}");
        $options = [
            CURLOPT_URL => (str_contains($endpoint, 'ramp')
                ? $this->rampUrl
                : $this->baseUrl) . "/{$endpoint}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $this->headers,
        ];

        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
            $options[CURLOPT_HTTPHEADER][] = "Content-Type: application/json";
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        \Log::info($response);
        if ($err) {
            return ["error" => $err];
        }

        return json_decode($response, true);
    }

    public function get($endpoint, $params = [])
    {
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }
        return $this->request('GET', $endpoint);
    }

    public function post($endpoint, $data = [])
    {
        return $this->request('POST', $endpoint, $data);
    }

    public function put($endpoint, $data = [])
    {
        return $this->request('PUT', $endpoint, $data);
    }
}
