<?php

namespace App\Http\Controllers\Api\V1\User;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Http\Helpers\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\TemporaryData;
use App\Traits\PinValidationTrait;
use App\Services\SafeHavenService;
use App\Traits\ControlDynamicInputFields;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InternalTransferController extends Controller
{
    use ControlDynamicInputFields, PinValidationTrait;

    protected $safeHavenService;

    public function __construct(SafeHavenService $safeHavenService)
    {
        $this->safeHavenService = $safeHavenService;
    }

    /**
     * Method for check user for P2P transfer
     * @param Illuminate\Http\Request $request
     */
    public function checkUser(Request $request){
        $validator = Validator::make($request->all(), [
            'username_or_email' => 'required|string',
        ]);
        if($validator->fails()) return Response::errorResponse("Validation Error", $validator->errors()->all());
        $validated = $validator->validate();

        $user = User::where('username', $validated['username_or_email'])
                    ->orWhere('email', $validated['username_or_email'])
                    ->first();

        if(!$user) return Response::errorResponse("User not found", [], 404);
        // if($user->id == Auth::id()) return Response::errorResponse("You cannot transfer money to yourself", [], 400);

        // Create simple Beneficiary Info for P2P
        $data['beneficiary'] = [
            'username' => $user->username,
            'fullname' => $user->fullname, // Using the accessor attribute
            'email'    => $user->email,
        ];
        
        // Convert to object for consistency
        $data['beneficiary'] = (object) $data['beneficiary'];

        $temp_identifier = generate_unique_string('temporary_datas','identifier',60);

        try{
            $temp_data = TemporaryData::create([
                'type'          => GlobalConst::FUND_TRANSFER,
                'identifier'    => $temp_identifier,
                'data'          => $data,
            ]);
        }catch(Exception $e) {
            return Response::errorResponse("Something went wrong!. Please try again.", [], 400);
        }
        return Response::successResponse("User found successfully.",[ 
            'temp_data'     => $temp_data,
            'user'          => $user->only(['id', 'firstname', 'lastname', 'username', 'email', 'image'])
        ],200);
    }
    /**
     * Method for confirm transfer (Merged Step 2 & 3)
     * @param Illuminate\Http\Request $request
     */
    public function confirm(Request $request) {
        $validator = Validator::make($request->all(), [
            'identifier'    => 'required|exists:temporary_datas,identifier',
            'amount'        => 'required|numeric|gt:0',
            'currency_code' => 'required|string|in:USD,NGN',
            'pin_code'      => 'nullable|string', 
        ]);
        if($validator->fails()) return Response::errorResponse("Validation Error", $validator->errors()->all());
        $validated = $validator->validate();

        // Optional PIN Check
        if($request->has('pin_code') && !empty($request->pin_code)) {
            $user = auth()->user();
            if (!$this->validateTransactionPin($user, $request->pin_code)) {
                 return Response::errorResponse("Invalid Transaction PIN", [], 400);
            }
        }

        $temp_data = TemporaryData::where('identifier', $validated['identifier'])->first();
        if(!$temp_data) return Response::errorResponse("Invalid Request", [], 400);

        // Sender Wallet Check
        $sender_wallet = \App\Models\UserWallet::auth()->active()->where('currency_code', $validated['currency_code'])->first();
        if(!$sender_wallet) return Response::errorResponse("Your " . $validated['currency_code'] . " wallet not found or inactive.", [], 404);

        if($sender_wallet->balance < $validated['amount']) return Response::errorResponse("Your wallet balance is insufficient", [], 400);

        // Receiver Wallet Check
        $receiver_username = $temp_data->data->beneficiary->username;
        $receiver = User::where('username', $receiver_username)->first();
        if(!$receiver) return Response::errorResponse("Receiver not found", [], 404);

        $receiver_wallet = \App\Models\UserWallet::where('user_id', $receiver->id)->where('currency_code', $validated['currency_code'])->active()->first();
        if(!$receiver_wallet) return Response::errorResponse("Receiver does not have an active " . $validated['currency_code'] . " wallet.", [], 400);

        // Calculate Charges
        $charges = (object)[
            'request_amount'    => (float)$validated['amount'],
            'sender_currency'   => $validated['currency_code'],
            'receiver_amount'   => (float)$validated['amount'],
            'receiver_currency' => $validated['currency_code'],
            'fixed_charge'      => 0,
            'percent_charge'    => 0,
            'total_charge'      => 0,
            'payable'           => (float)$validated['amount'],
        ];

        // Transaction Execution
        $trx_id = 'IT-' . Str::random(12);

        DB::beginTransaction();
        try {

            // Check if actual Bank Transfer is needed (NGN only)
            $safeHavenDone = false;
            
            if ($validated['currency_code'] === 'NGN') {
                $senderSubAccount = $user->virtualAccounts()->where('provider', 'safehaven')->first();
                $receiverSubAccount = $receiver->virtualAccounts()->where('provider', 'safehaven')->first();

                if ($senderSubAccount && $receiverSubAccount) {
                    // Both users have SafeHaven accounts. Execute Bank Transfer.
                    // Use SafeHaven MFB Code - filter out invalid codes like '000000'
                    $bankCode = $receiverSubAccount->bank_code ?? '090286';
                    // Validate and fix invalid bank codes
                    if (empty($bankCode) || $bankCode === '000000' || strlen($bankCode) !== 6) {
                        $bankCode = '090286'; // SafeHaven MFB Code
                    }
                    
                    // 1. Name Enquiry
                    $enquiry = $this->safeHavenService->nameEnquiry($bankCode, $receiverSubAccount->account_number);
                    $sessionId = $enquiry['sessionId'] ?? ($enquiry['sessionID'] ?? null);

                    if (!$sessionId) {
                        throw new Exception("Beneficiary account validation failed.");
                    }

                    // 2. Transfer
                    $reference = 'IT-' . Str::random(12);
                    $payload = [
                        "saveBeneficiary" => false,
                        "nameEnquiryReference" => $sessionId,
                        "debitAccountNumber" => $senderSubAccount->account_number,
                        "beneficiaryBankCode" => $bankCode,
                        "beneficiaryAccountNumber" => $receiverSubAccount->account_number,
                        "amount" => (float)$charges->payable, // Send full payable (including fees if any, but internal usually 0)
                        "narration" => "In-App Transfer from " . $user->username,
                        "paymentReference" => $reference
                    ];

                    \Log::info("Calling SafeHaven Transfer", ['payload' => $payload]);
                    $transferRes = $this->safeHavenService->transfer($payload);
                    \Log::info("SafeHaven Transfer Result", ['result' => $transferRes]);
                    
                    $safeHavenDone = true;
                    // Note: We do NOT manually credit the receiver wallet here because SafeHaven will likely send a webhook 
                    // for the incoming credit to the receiver's account.
                    // We DO debit the sender wallet to reflect money leaving.
                }
            }


            // Debit Sender
            $sender_wallet->balance -= $charges->payable;
            $sender_wallet->save();

            // Credit Receiver (Only if NOT handled by SafeHaven Webhook)
            if (!$safeHavenDone) {
                $receiver_wallet->balance += $charges->request_amount;
                $receiver_wallet->save();
            }

            // Create Transaction Record (Sender)
            \App\Models\Transaction::create([
                'type' => \App\Constants\PaymentGatewayConst::TYPE_OWN_BANK_TRANSFER, 
                'trx_id' => $trx_id,
                'user_type' => GlobalConst::USER,
                'user_id' => $sender_wallet->user_id,
                'wallet_id' => $sender_wallet->id,
                'request_amount' => $charges->request_amount,
                'request_currency' => $charges->sender_currency,
                'exchange_rate' => 1,
                'total_charge' => $charges->total_charge,
                'total_payable' => $charges->payable,
                'available_balance' => $sender_wallet->balance,
                'receive_amount' => $charges->request_amount,
                'receiver_type' => GlobalConst::USER,
                'receiver_id' => $receiver->id,
                'payment_currency' => $charges->sender_currency,
                'details' => json_encode(['beneficiary' => $temp_data->data->beneficiary]),
                'status' => \App\Constants\PaymentGatewayConst::STATUSSUCCESS,
                'attribute' => GlobalConst::SEND,
                'created_at' => now(),
            ]);

            // Create Transaction Record (Receiver)
             \App\Models\Transaction::create([
                'type' => \App\Constants\PaymentGatewayConst::TYPE_OWN_BANK_TRANSFER,
                'trx_id' => $trx_id,
                'user_type' => GlobalConst::USER,
                'user_id' => $receiver_wallet->user_id,
                'wallet_id' => $receiver_wallet->id,
                'request_amount' => $charges->request_amount,
                'request_currency' => $charges->receiver_currency,
                'exchange_rate' => 1,
                'total_charge' => 0,
                'total_payable' => 0, 
                'available_balance' => $receiver_wallet->balance,
                'receive_amount' => $charges->request_amount,
                'receiver_type' => GlobalConst::USER,
                'receiver_id' => $sender_wallet->user_id, 
                'payment_currency' => $charges->receiver_currency,
                'details' => json_encode(['sender' => $sender_wallet->user->username]),
                'status' => \App\Constants\PaymentGatewayConst::STATUSSUCCESS, // Will be updated to match webhook if needed, but for internal tracking we mark success
                'attribute' => GlobalConst::RECEIVED,
                'created_at' => now(),
            ]);
            
            $temp_data->delete();
            DB::commit();

        } catch (Exception $e) {
            \Log::error("Transfer Error: " . $e->getMessage() . "\nStack: " . $e->getTraceAsString());
            DB::rollBack();
            return Response::errorResponse("Transaction failed! " . $e->getMessage(), [], 400);
        }


        return Response::successResponse("Transfer successful", [
            'transaction' => [
                'trx_id' => $trx_id,
                'amount' => $charges->request_amount,
                'currency' => $validated['currency_code'],
                'sender' => [
                    'username' => $user->username,
                    'available_balance' => $sender_wallet->balance,
                ],
                'receiver' => [
                    'username' => $receiver->username,
                    'fullname' => $receiver->fullname,
                ],
                'status' => 'success',
                'type' => 'internal_transfer',
                'safehaven_transfer' => $safeHavenDone,
            ]
        ], 200);
    }
}
