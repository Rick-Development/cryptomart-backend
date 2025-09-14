<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Http\Helpers\Response;
use App\Services\QuidaxService;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Constants\PaymentGatewayConst;

class QuidaxController extends Controller
{
    public $quidax;

    public function __construct(QuidaxService $quidax)
    {
        $this->quidax = $quidax;
    }


    public function getUser()
    {
        $response = $this->quidax->getUser();

        return Response::success('User  data fetch successfully!', $response['data']);
    }



    public function fetchUserWallets(Request $request)
    {
        $response = $this->quidax->fetchUserWallets(auth()->user()->quidax_id);
        return Response::success('Wallets fetch successfully!', $response['data']);
    }

    //  fetchUserWallet($quidax_id,$currency)
    public function fetchUserWallet(Request $request)
    {
        $response = $this->quidax->fetchUserWallet(auth()->user()->quidax_id, $request->currency);
        return Response::success('Wallet fetch successfully!', $response['data']);
    }


    public function fetchPaymentAddress(Request $request)
    {
        $response = $this->quidax->fetchPaymentAddress(auth()->user()->quidax_id, $request->currency);
        return Response::success('Fetch successfully!', $response['data']);
    }
    public function fetchPaymentAddressses(Request $request)
    {
        $response = $this->quidax->fetchPaymentAddressses(auth()->user()->quidax_id, $request->currency);
        return Response::success($response['message'], $response['data']);
    }
    public function createCryptoPaymentAddress(Request $request)
    {
        $response = $this->quidax->createCryptoPaymentAddress(auth()->user()->quidax_id, $request->currency, [
            'network' => $request->network
        ]);
        return Response::success($response['message'], $response['data']);
    }
    public function createSwapQuotation(Request $request)
    {
        $response = $this->quidax->createSwapQuotation(auth()->user()->quidax_id, [
            'from_currency' => $request->from_currency,
            'to_currency' => $request->to_currency,
            'from_amount' => $request->from_amount,
            // 'to_amount' => '11'
        ]);
        return Response::success($response['message'], $response['data']);
    }
    public function swap(Request $request)
    {
        $response = $this->quidax->swap(auth()->user()->quidax_id, $request->quotation_id);
        // dd($response);
        return Response::success($response['message'], $response['data']);
    }

    public function fetch_withdraws(Request $request)
    {
        if (!$request->status || !$request->currency) {
            return response()->json([
                'message' => 'status or currency param required',
            ]);
        }

        $response = $this->quidax->fetch_withdraws(auth()->user()->quidax_id, $request->currency, $request->status);
        return Response::success($response['message'], $response['data']);
    }

    public function cancel_withdrawal(Request $request)
    {
        $response = $this->quidax->cancel_withdrawal(auth()->user()->quidax_id, $request->withdrawal_id);
        return Response::success($response['message'], $response['data']);
    }

    public function initiate_ramp_transaction(Request $request)
    {
        $data = [
            'from_currency' => $request->from_currency,
            'to_currency' => $request->to_currency,
            'from_amount' => $request->from_amount,
            'merchant_reference' => $request->merchant_reference,

            'customer' => [
                'email' => $request->customer_email,
                'first_name' => $request->customer_first_name,
                'last_name' => $request->customer_last_name,
            ],

            'wallet_address' => [
                'address' => $request->wallet_address,
                'network' => $request->wallet_network,
            ],
        ];
        $response = $this->quidax->initiate_ramp_transaction($data);
        // dd($response);
        return Response::success(
            $response['message'],
            [
                'api_data' => $response['data'],
                'user_data' => $data,
            ]
        );
    }

    public function refresh_instant_swap_quotation(Request $request)
    {
        if (!$request->quotation_id) {
            return response()->json([
                'message' => 'quotation_id param required',
            ]);
        }

        $data = [
            'from_currency' => $request->from_currency,
            'to_currency' => $request->to_currency,
            'from_amount' => $request->from_amount
        ];

        $response = $this->quidax->refresh_instant_swap_quotation(auth()->user()->quidax_id, $request->quotation_id, $data);
        // dd($response);
        return Response::success($response['message'], $response['data']);
    }

    public function fetch_swap_transaction(Request $request)
    {
        if (!$request->transaction_id) {
            return response()->json([
                'message' => 'transaction_id param required',
            ]);
        }

        $response = $this->quidax->fetch_swap_transaction(auth()->user()->quidax_id, $request->transaction_id);
        // dd($response);
        return Response::success($response['message'], $response['data']);
    }
    // createCryptoPaymentAddress($quidax_id,$currency,$data)

    // fetchPaymentAddress($quidax_id,$currency){

}
