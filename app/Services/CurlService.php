<?php

namespace App\Services;

class CurlService
{
    protected $baseUrl;
    protected $headers;

    public function __construct($baseUrl, $token = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->headers = [
            "accept: application/json",
        ];

        if ($token) {
            $this->headers[] = "Authorization: Bearer {$token}";
        }
    }

    protected function request($method, $endpoint, $data = [])
    {
        $curl = curl_init();
        \Log::info("{$this->baseUrl}/{$endpoint}");
        $options = [
            CURLOPT_URL => "{$this->baseUrl}/{$endpoint}",
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
