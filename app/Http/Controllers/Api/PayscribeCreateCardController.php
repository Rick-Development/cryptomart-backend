<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\CardIssusing\CreateCardHelper;
use App\Http\Helpers\Payscribe\PayscribeBalanceHelper;
use App\Models\BasicControl;
use App\Models\PayscribeVirtualCardDetails;
use App\Models\PayscribeVirtualCardTransaction;
use App\Models\Transaction;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayscribeCreateCardController extends Controller
{
    use Notify;

    private $modelPath = 'PayscribeCardIssuing';

    public function __construct(private CreateCardHelper $createCardHelper, private PayscribeBalanceHelper $payscribeBalanceHelper)
    {
    }

    public function createCard(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'customer_id' => 'sometimes|string',
            'currency' => 'sometimes|string',
            'brand' => 'required|string',
            'amount' => 'required',
            'type' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation failed',
                'data' => $validator->errors(),
            ], 422);
        }

        $data = $request->only(
            [
                'customer_id',
                "currency",
                "brand",
                "amount",
                "type",
            ]
        );

        $user = auth()->user();
        $user_as_card = PayscribeVirtualCardDetails::where('user_id', $user['id'])->value('card_id');
        if (!!$user_as_card) {
            return response()->json([
                "message" => "Customer card Exist"
            ]);
        }

        // Validate the user's balance before proceeding
        $cardIssuingRate = (int) BasicControl::first()->card_issuing_rate;
        $cardDepositRate = (int) BasicControl::first()->card_deposit_rate;
        $depositAmount = $data['amount'] * $cardDepositRate;

        $totalCharged = $depositAmount + $cardIssuingRate;

        $validateBalance = $this->payscribeBalanceHelper->validateBalance($totalCharged);

        if (!!$validateBalance) {
            return $validateBalance;
        }

        $data['customer_id'] = auth()->user()->payscribe_customer_id;
        // Generate a UUID
        $referenceId = Str::uuid();
        $referenceIdString = (string) $referenceId . '-cardIssuing';
        $data = array_merge($data, ['ref' => $referenceIdString]);

        $response = json_decode($this->createCardHelper->createCard($data), true);

        if ($response['status'] === true) {
            // Create a transaction record for the card issuing
            $this->cardIssuingTransaction($data, $response, $totalCharged);
            $this->virtualCardDetails($response, $data);
            $params = [
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'transaction' => 'processing',
            ];
            $this->mail($user, 'VIRTUAL_CARD_APPLY', $params);
        }

        return $response;
    }


    public function virtualCardDetails($response, $request)
    {
        PayscribeVirtualCardDetails::create([
            'user_id' => auth()->user()->id,
            'card_id' => $response['message']['details']['card']['id'],
            'card_type' => $response['message']['details']['card']['card_type'],
            'currency' => $response['message']['details']['card']['currency'],
            'brand' => $response['message']['details']['card']['brand'],
            'card_name' => $response['message']['details']['card']['name'],
            'masked' => $response['message']['details']['card']['masked'],
            'card_number' => $response['message']['details']['card']['number'],
            'expiry_date' => $response['message']['details']['card']['expiry'],
            'ccv' => $response['message']['details']['card']['ccv'],
            'billing_address' => $response['message']['details']['card']['billing'],
            'trans_id' => $response['message']['details']['trans_id'],
            'ref' => $response['message']['details']['ref'],
            'balance' => $request['amount'],
        ]);
    }
    private function createTransaction($request, $response, $totalamount)
    {
        $transId = $response['message']['details']['trans_id'];
        Transaction::create([
            'transactional_type' => 'Card Issuing',
            'user_id' => auth()->user()->id,
            'amount' => $totalamount,
            'currency' => 'NGN',
            'trx_type' => '-',
            'remarks' => 'You have successfully funded your card with ' . $request['amount'] . ' USD',
            'trx_id' => $transId,
            'transaction_status' => 'proccessing',
        ]);
    }

    private function cardIssuingTransaction($request, $response, $totalamount)
    {
        $transId = $response['message']['details']['trans_id'];
        Transaction::create([
            'transactional_type' => 'Card Issuing',
            'user_id' => auth()->user()->id,
            'amount' => $totalamount,
            'currency' => 'NGN',
            'trx_type' => '+',
            'remarks' => 'Card Issuing at ' . $totalamount . ' NGN',
            'trx_id' => $transId,
            'ref_id' => $request['ref'],
            'transaction_status' => 'processing',
        ]);

        // $this->payscribeBalanceHelper->updateUserBalance($balance);
    }



    public function cardIssuingRate()
    {
        $cardIssuingRate = BasicControl::first()->card_issuing_rate;
        return response()->json([
            'status' => true,
            'message' => 'Card Issuing Rate',
            'data' => $cardIssuingRate,
        ], 200);
    }

    public function cardDepositRate()
    {
        $cardDepositRate = BasicControl::first()->card_deposit_rate;
        return response()->json([
            'status' => true,
            'message' => 'Card Issuing Rate',
            'data' => $cardDepositRate,
        ], 200);
    }

    public function cardWithdrawalRate()
    {
        $cardWithdarwalRate = BasicControl::first()->card_withdrawal_rate;
        return response()->json([
            'status' => true,
            'message' => 'Card Issuing Rate',
            'data' => $cardWithdarwalRate,
        ], 200);
    }



    public function customerTransactions(string $cardId)
    {

        $transactions = PayscribeVirtualCardTransaction::where('card_id', $cardId)->paginate(10);
        return response()->json(['transactions' => $transactions]);
        // return response()->json(['transactions' => auth()->id()]);
    }

    public function customerCardDetails()
    {

        $transactions = PayscribeVirtualCardDetails::where('user_id', auth()->id())->paginate(10);
        return response()->json(['transactions' => $transactions]);
        // return response()->json(['transactions' => auth()->id()]);
    }
}
