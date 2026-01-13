<?php
namespace App\Http\Helpers\SafeHeaven;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiConnectionHelper{

    private $apiClientId;
    private $apiClientAssertion;
    private $apiAuthUrl;

    private $cacheKey = 'safeheaven_auth_token';


    public function __construct() {
        $basicSettings = \App\Models\Admin\BasicSettings::first();
        
        $this->apiClientId = $basicSettings->safehaven_client_id ?? trim(config('services.safeHeaven.client_id'));
        $this->apiClientAssertion = $basicSettings->safehaven_client_assertion ?? trim(config('services.safeHeaven.client_assertion'));
        $this->apiAuthUrl = rtrim($basicSettings->safehaven_api_url ?? config('services.safeHeaven.api_url'), '/');
    }


// public function authentication(){

//     $authCrediental = [
//         "grant_type" => "client_credentials",
//         "client_id"=> $this->apiClientId,
//         "client_assertion_type"=> "urn:ietf:params:oauth:client-assertion-type:jwt-bearer",
//         "client_assertion" => $this->apiClientAssertion,
//     ];

//       $jsonData = json_encode($authCrediental);

//     $curl = curl_init();

//     curl_setopt_array($curl, [
//         CURLOPT_URL => $this->apiAuthUrl . "/oauth2/token",
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_ENCODING => "",
//         CURLOPT_MAXREDIRS => 10,
//         CURLOPT_TIMEOUT => 30,
//         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//         CURLOPT_CUSTOMREQUEST => "POST",
//         CURLOPT_POSTFIELDS => $jsonData,
//         CURLOPT_HTTPHEADER => [
//             "Accept: application/json",
//             "Content-Type: application/json"
//         ],
//     ]);

//     $response = curl_exec($curl);
//     $err = curl_error($curl);

//     curl_close($curl);
//     $decodedResponse = json_decode($response,true);
//     return $decodedResponse;

// }

  
public function authentication()
{
    $tokenData = Cache::get($this->cacheKey);

    if ($tokenData) {
        $createdAt = $tokenData['created_at'];
        $expiresIn = $tokenData['expires_in'];
        $expiresAt = $createdAt + $expiresIn;

        if (now()->timestamp < $expiresAt - 60) { // renew 1 min before expiry
            return $tokenData;
        }
    }

    // If no valid token, get a new one
    $authCrediental = [
        "grant_type" => "client_credentials",
        "client_id" => $this->apiClientId,
        "client_assertion_type" => "urn:ietf:params:oauth:client-assertion-type:jwt-bearer",
        "client_assertion" => $this->apiClientAssertion,
    ];

    $jsonData = json_encode($authCrediental);

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $this->apiAuthUrl . "/oauth2/token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "Content-Type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        throw new \Exception("cURL Error: $err");
    }

    $decodedResponse = json_decode($response, true);

    if (!isset($decodedResponse['access_token'])) {
        throw new \Exception("Failed to obtain access token: " . $response);
    }

    $newTokenData = [
        'access_token' => $decodedResponse['access_token'],
        'expires_in' => $decodedResponse['expires_in'],
        'ibs_client_id' => $decodedResponse['ibs_client_id'],
        'created_at' => now()->timestamp,
    ];

    // Store in cache, set slightly less than expires_in
    Cache::put($this->cacheKey, $newTokenData, $decodedResponse['expires_in'] - 60);

    return $newTokenData;
}


public function get($url){
    $auth = $this->authentication();
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $this->apiAuthUrl . $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer {$auth['access_token']}",
        "ClientID: {$auth['ibs_client_id']}"
    ),
    ));

    $response = curl_exec($curl);
    Log::info("SafeHaven GET Response", ['url' => $this->apiAuthUrl . $url, 'response' => $response]);

    curl_close($curl);
    return $response;

}
public function post($url, array $data) {
    $auth = $this->authentication();
    $curl = curl_init();
    
    
    // Convert the data array to JSON
    $jsonData = json_encode($data);
    Log::info("SafeHaven POST Request", ['url' => $this->apiAuthUrl . $url, 'payload' => $data]);
    
    curl_setopt_array($curl, array(
        CURLOPT_URL => $this->apiAuthUrl . $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => array(
             "Authorization: Bearer {$auth['access_token']}",
            "ClientID: {$auth['ibs_client_id']}",
            "Content-Type: application/json", // Set the content type to JSON
            "Content-Length: " . strlen($jsonData) // Set the content length
        ),
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);

    if ($err) {
        Log::error("SafeHaven POST Error: " . $err, ['url' => $this->apiAuthUrl . $url]);
    }

    Log::info("SafeHaven POST Response", ['url' => $this->apiAuthUrl . $url, 'response' => $response]);
    
    curl_close($curl);

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if($httpCode >= 400) {
        Log::error("SafeHaven POST HTTP Error $httpCode", ['url' => $this->apiAuthUrl . $url, 'response' => $response]);
    }

    return $response;
}

public function patch($url,$data){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'PATCH',
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer ".$this->authentication()['access_token'],
        "ClientID :" .$this->authentication()['ibs_client_id']    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;

}

public function put($url,$data){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer ".$this->authentication()['access_token'],
        "ClientID :" .$this->authentication()['ibs_client_id']    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;

}

}