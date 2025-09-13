<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class QuidaxService
{
    protected $baseUrl;
    protected $apiSecret;
    protected $curl;

    public function __construct()
    {
        $this->baseUrl = config('services.quidax.url');
        $this->apiSecret = config('services.quidax.secret');

        $this->curl = new CurlService(
            $this->baseUrl,
            $this->apiSecret
        );
    }


    public function createSubAccount($data){
        //  [
        //     'email' => 'test@gmail.com',
        //     'first_name' => 'test',
        //     'last_name' => 'user'
        //   ]
        return $this->curl->post("v1/users",$data);
    }
    public function getUser()
    {
        return $this->curl->get("v1/users/me");
    }

    public function fetchUserWallets($quidax_id)
    {
        return $this->curl->get("v1/users/{$quidax_id}/wallets");
    }

    public function fetchUserWallet($quidax_id,$currency)
    {
        return $this->curl->get("v1/users/{$quidax_id}/wallets/{$currency}");
    }

    public function fetchPaymentAddress($quidax_id,$currency){
        return $this->curl->get("v1/users/{$quidax_id}/wallets/{$currency}/address");
    }
    public function fetchPaymentAddressses($quidax_id,$currency){
        return $this->curl->get("v1/users/{$quidax_id}/wallets/{$currency}/addresses");
    }


    public function createCryptoPaymentAddress($quidax_id,$currency,$data){
          return $this->curl->post("v1/users/me/wallets/btc/addresses",$data);
    }

    public function createSwapQuotation($quidax_id,$data){
          return $this->curl->post("v1/users/{$quidax_id}/swap_quotation",$data);
    }

    public function swap($quidax_id,$quotation_id){
          return $this->curl->post("v1/users/{$quidax_id}/swap_quotation/{$quotation_id}/confirm");
    }

    public function fetch_withdraws($quidax_id, $currency, $status)
    {
        return $this->curl->get("v1/users/{$quidax_id}/withdraws?order_by=asc&currency={$currency}&state={$status}");
    }

    public function cancel_withdrawal($withdrawal_id, $quidax_id)
    {
        return $this->curl->get("v1/users/{$quidax_id}/withdraws/{$withdrawal_id}/cancel");
    }

    public function initiate_ramp_transaction()
    {
        return $this->curl->get("v1/merchants/custodial/on_ramp_transactions/initiate");
    }
}
