<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\CardIssusing\WithdrawFromCardHelper;
use App\Http\Helpers\Payscribe\PayscribeBalanceHelper;
use App\Models\BasicControl;
use App\Models\PayscribeVirtualCardDetails;
use App\Models\PayscribeVirtualCardTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayscribeWithdrawFromCardController extends Controller
{
    public function __construct(private WithdrawFromCardHelper $withdrawFromCardHelper, private PayscribeBalanceHelper $payscribeBalanceHelper){}
    public function withdraw(Request $request){
        $request->validate(
            [
                'amount' => 'required | numeric | min:0.1',
            ]
        );
        $cardId = PayscribeVirtualCardDetails::where('user_id', auth()->user()['id'])->value('card_id');

        $cardWithdarawlRate = (int) BasicControl::first()->card_withdrawal_rate;
        $withdrawalAmount = $request['amount'] * $cardWithdarawlRate;

        $referenceId = Str::uuid();
        $referenceIdString = (string) $referenceId . '-cardwithdrawal';
        $data = [
            'amount' => $request['amount'],
            'ref' => $referenceIdString,
        ];
        $response = json_decode($this->withdrawFromCardHelper->withdrawFromCard($data, $cardId), true);

        if($response['status'] === true){
            $this->createTransaction($data, $response, $withdrawalAmount);

            $this->cardWithdrawalTransaction($data, $response);
            $this->sendCardWithdrawalEmail($request['amount'], $response['message']['details']['card'], $response['message']['details']['trans_id']);
        }
        return $response;
    }

    private function createTransaction($request, $response, $withdrawalAmount) {
        $transId = $response['message']['details']['trans_id'];
        Transaction::create([
            'transactional_type' => 'Card Withdrawal',
            'user_id' => auth()->user()->id,
            'amount' => $withdrawalAmount,
            'currency' => 'NGN',
            'trx_type' => '+',
            'remarks' => 'You have successfully cedited your allet with ' . $request['amount'] . ' USD',
            'trx_id' => $transId,
            'ref_id' => $request['ref'],
            'transaction_status' => 'processing',
        ]);

    }


    private function cardWithdrawalTransaction($request, $response) {
        $balance = $response['message']['details']['card']['balance'];
        $transId = $response['message']['details']['trans_id'];
        $refId = $response['message']['details']['ref_id'];
        $cardId = $response['message']['details']['card']['id'];
        PayscribeVirtualCardTransaction::create([
            'transactional_type' => 'Card Withdrawal',
            'user_id' => auth()->user()->id,
            'card_id' => $cardId,
            'amount' => $request['amount'],
            'currency' => 'USD',
            'balance' => $balance,
            'charge' => 0.0,
            'trx_type' => '-',
            'remarks' => $response['description'],
            'trx_id' => $transId,
            'ref' => $refId,
            'event_id' => $response['message']['details']['event_id'],
            'action' => $response['message']['details']['action'],
        ]);

    }
}