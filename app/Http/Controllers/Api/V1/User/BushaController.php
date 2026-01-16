<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BushaTransaction;
use App\Models\UserWallet;
use App\Services\BushaService;
use App\Http\Helpers\Response;
use App\Services\QuidaxService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\BushaPaymentDetail;

class BushaController extends Controller
{
    protected $bushaService;
    public $quidaxService;

    public function __construct(BushaService $bushaService, QuidaxService $quidaxService)
    {
        $this->bushaService = $bushaService;
        $this->quidaxService = $quidaxService;
    }



    /**
     * Get Quote
     * Returns a valid quote ID that can be used to execute the trade.
     * 
     * Expected Request Parameters:
     * - source_currency: The currency being spent (e.g., 'BTC' for sell, 'NGN' for buy)
     * - target_currency: The currency being received (e.g., 'NGN' for sell, 'BTC' for buy)
     * - amount: The amount (will be interpreted as source_amount for sell, target_amount for buy)
     * - side: 'buy' or 'sell'
     * - user_address (optional): User's crypto wallet address for receiving crypto
     * - network (optional): Blockchain network (e.g., 'BTC', 'ETH')
     */
    public function quote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source_currency' => 'required|string', 
            'target_currency' => 'required|string', 
            'amount' => 'required|numeric|min:0',
            'side' => 'required|in:buy,sell',
            'user_address' => 'nullable|string', // User's wallet address for crypto
            'network' => 'nullable|string', // Blockchain network
        ]);

        if ($validator->fails()) {
            return Response::errorResponse($validator->errors()->all());
        }

        try {
            $user = auth()->user();
            $reference = 'QUO_' . Str::random(10);
            
            // Determine if source or target is crypto
            $cryptoCurrencies = ['BTC', 'ETH', 'USDT', 'USDC', 'LTC', 'DOGE', 'XRP', 'BNB'];
            $sourceCurrency = strtoupper($request->source_currency);
            $targetCurrency = strtoupper($request->target_currency);
            
            $sourceIsCrypto = in_array($sourceCurrency, $cryptoCurrencies);
            $targetIsCrypto = in_array($targetCurrency, $cryptoCurrencies);
            
            // Build pay_in and pay_out based on transaction type
            $pay_in = [];
            $pay_out = [];
            
            if ($request->side == 'buy') {
                // BUY: User spends FIAT (or crypto) to get CRYPTO
                // Pay In: Source currency (what user pays with)
                // Pay Out: Target currency (crypto user receives)
                
                if ($sourceIsCrypto) {
                    // Paying with crypto (e.g., BTC to buy ETH)
                    $pay_in = [
                        "type" => "balance", // Using Busha balance
                    ];
                } else {
                    // Paying with fiat (e.g., NGN to buy BTC)
                    $pay_in = [
                        "type" => "temporary_bank_account", // Using Busha fiat balance
                    ];
                }
                
                // $response = $this->quidaxService = 
                
                $response = $this->quidaxService->fetchPaymentAddressses(auth()->user()->quidax_id, strtolower($request->target_currency));
                // \Log::info($response);
                $data = $response['data'];
                $data = array_filter($data, function ($item) use ($request) {
                    return $item['network'] === strtolower($request->network);
                });

                if (empty($data)) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'No address found',
                        'data' => []
                    ], 422);
                }
                
                // Get the first (and only) matching address
                $data = reset($data);
                
                // Receiving crypto
                $pay_out = [
                    "type" => "address",
                    "address" => $data['address'], // Use user's address or email
                    "network" => $request->network ?? $targetCurrency,
                ];
                
            } else {

                // SELL: User spends CRYPTO to get FIAT (or another crypto)
                // Pay In: Source currency (crypto user sells)
                // Pay Out: Target currency (fiat/crypto user receives)
                
                // Paying with crypto
                $pay_in = [
                    "type" => "address",
                    "network" => $request->network ?? $sourceCurrency,
                ];
                
                if ($targetIsCrypto) {
                    // Receiving crypto (e.g., sell BTC for ETH)
                    $pay_out = [
                        "type" => "address",
                        "address" => $request->user_address ?? $user->email,
                        "network" => $targetCurrency,
                    ];
                } else {
                    if ($user->busha_recipient_id == null) {
                     return $recipient = $this->bushaService->createRecipient($user);
                      if ($recipient == false) {
                        return Response::errorResponse('Failed to create trading account');
                      }
                        
                    }
                    // Receiving fiat (e.g., sell BTC for NGN)
                    $pay_out = [
                        "type" => "bank_transfer", // Credit to Busha fiat balance
                        'recipient_id' => $user->busha_recipient_id,
                    ];
                }
            }
            
            // Build the payload
            $payload = [
                "source_currency" => $sourceCurrency,
                "target_currency" => $targetCurrency,
                "reference" => $reference,
                "pay_in" => $pay_in,
                "pay_out" => $pay_out
            ];
            
            // Add amount based on side
            // For BUY: User specifies how much crypto they want to receive (target_amount)
            // For SELL: User specifies how much crypto they want to sell (source_amount)
            if ($request->side == 'buy') {
                $payload['target_amount'] = (string)$request->amount;
            } else {
                $payload['source_amount'] = (string)$request->amount;
            }
            
            // Call Busha API
            $bushaResponse = $this->bushaService->quote($payload);

            // \Log::info('Busha quote response', $bushaResponse);
            
            // Check for errors
            if (isset($bushaResponse['error'])) {
                return Response::errorResponse(
                    $bushaResponse['error']['message'] ?? 'Failed to create quote',
                    $bushaResponse['fields'] ?? []
                );
            }
            
            return Response::successResponse($bushaResponse['message'], $bushaResponse['data']);
            
        } catch (Exception $e) {
            \Log::error('Quote creation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Execute Trade
     * Accepts a quote_id returned from quote endpoint.
     */
    public function trade(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quote_id' => 'required|string',
            'side' => 'required|in:buy,sell',
        ]);

        if ($validator->fails()) {
            return Response::errorResponse($validator->errors()->all());
        }

        $user = auth()->user();
        $reference = Str::uuid();

        DB::beginTransaction();
        try {
            // 0. Fetch Quote Details to Verify Amounts
            // We MUST trust the source of truth (Busha) or our own DB, not the Request for amounts.
            $quoteDetails = $this->bushaService->getQuoteDetails($request->quote_id)['data'];
            
            // Expected structure of quoteDetails: ['id' => ..., 'source_currency' => ..., 'target_currency' => ..., 'source_amount' => ..., 'target_amount' => ...]
            // Adjust based on actual API response structure.
            
            $side = $quoteDetails['rate']['side'];
            $sourceCurrency = $quoteDetails['source_currency'];
            $targetCurrency = $quoteDetails['target_currency'];
            $sourceAmount = $quoteDetails['source_amount'];
            $targetAmount = $quoteDetails['target_amount'];
            
            // 1. Debit Logic
            // If Side is BUY (User buys crypto with Fiat/Crypto):
            // The quote logic was: Source = What user PAYS/SPENDS. Target = What user GETS.
            // So we ALWAYS debit the SOURCE amount and currency.
            
            // Verify wallet existence and balance
             $wallet = UserWallet::where('user_id', $user->id)->whereHas('currency', function($q) use ($sourceCurrency) {
                $q->where('code', $sourceCurrency);
            })->first();

            if ($side == 'sell') {
                $wallet = UserWallet::where('user_id', $user->id)->whereHas('currency', function($q) use ($targetCurrency) {
                    $q->where('code', $targetCurrency);
                })->first();
            }else{
            // $wallet->balance = 10000000000;
            // $wallet->save();
            if (!$wallet || $wallet->balance < $sourceAmount) {
                throw new Exception("Insufficient $sourceCurrency balance. Required: $sourceAmount");
            }

            // Debit the user
            $wallet->balance -= $sourceAmount;
            $wallet->save();
        }
        return;
            // 2. Execute Transfer on Busha
            $transfer = $this->bushaService->executeQuote($request->quote_id, $reference);
            
            // 3. Record Transaction
            BushaTransaction::create([
                'id' => $reference,
                'user_id' => $user->id,
                'reference' => $reference,
                'busha_order_id' => $transfer['id'] ?? null,
                'type' => $request->side, // 'buy' or 'sell'
                'pair' => $sourceCurrency . '-' . $targetCurrency, // e.g. BTC-NGN
                'amount' => ($request->side === 'buy' ? $targetAmount : $sourceAmount), // Crypto Amount
                'total' => ($request->side === 'buy' ? $sourceAmount : $targetAmount), // Fiat/Other Amount
                'rate' => ($sourceAmount > 0 ? $targetAmount / $sourceAmount : 0), // Implied rate
                'status' => 'pending', 
                'metadata' => array_merge($transfer, ['quote' => $quoteDetails]),
            ]);
            
            // Note: The credits (Crypto for Buy, Fiat for Sell) should happen via Webhook OR immediately if we trust Busha.
            // Search result said "Execute...". Transfers usually go to "pending" or "completed".
            // To be safe, I'll let the Webhook handle the Credit.
            // BUT if the webhook is delayed, user waits.
            // If the user wants instant "Platform Buy/Sell", we might credit immediately if detailed status is 'completed'.
            // I'll stick to Webhook for Credit to be safe.

            DB::commit();
            return Response::successResponse('Trade executed successfully',$transfer['data']);

        } catch (Exception $e) {
            DB::rollBack();
            return Response::errorResponse($e->getMessage());
        }
    }

    public function getTransfer($id) {
        // If it looks like a Quote ID, try to find the Transfer ID from our records
        if (Str::startsWith($id, 'QUO_')) {
            $transaction = BushaTransaction::where('metadata->quote->id', $id)->first();
            
            if (!$transaction) {
                // Fallback: Maybe it's in the root of metadata (depending on how it was saved previously)
                // or maybe the user hasn't executed it yet.
                return Response::errorResponse('Transaction not found for this Quote ID. Please ensure the trade was executed.');
            }
            
            if (!$transaction->busha_order_id) {
                return Response::errorResponse('Trade found but no Transfer ID recorded.');
            }
            
            $id = $transaction->busha_order_id;
        }

        try {
            $transfer = $this->bushaService->getTransfer($id);
            return Response::successResponse('Transfer fetched', $transfer['data']);
        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    public function banks() {
        try {
            $banks = $this->bushaService->getBanks();
            return Response::successResponse('Banks fetched', ['banks' => $banks]);
        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }
    
    public function history() {
        $history = BushaTransaction::where('user_id', auth()->id())->orderByDesc('created_at')->paginate(20);
        return Response::successResponse('History fetched', ['transactions' => $history]);
    }

    /**
     * Add a new Bank Account for Payouts
     */
    public function addBankAccount(Request $request) {
        // return auth()->user()->busha_recipient_id;
        $validator = Validator::make($request->all(), [
            'bank_name'      => 'required|string',
            'bank_code'      => 'required|string',
            'account_number' => 'required|string',
            'account_name'   => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::errorResponse($validator->errors()->all());
        }

        try {
            $user = auth()->user();

            // 1. Create Recipient on Busha
            $recipientData = $this->bushaService->createPayoutRecipient([
                'bank_name'      => $request->bank_name,
                'bank_code'      => $request->bank_code,
                'account_number' => $request->account_number,
                'account_name'   => $request->account_name
            ]);

            $recipientId = $recipientData['data']['id'] ?? $recipientData['id'] ?? null;

            if (!$recipientId) {
                return Response::errorResponse('Failed to generate recipient ID from Busha.');
            }

            $user->busha_recipient_id = $recipientId;
            $user->save();
            // 2. Save to DB
            $account = BushaPaymentDetail::create([
                'user_id'        => $user->id,
                'bank_name'      => $request->bank_name,
                'bank_code'      => $request->bank_code,
                'account_number' => $request->account_number,
                'account_name'   => $request->account_name,
                'recipient_id'   => $recipientId,
                'currency'       => 'NGN',
                'is_default'     => BushaPaymentDetail::where('user_id', $user->id)->count() === 0 // Make default if first one
            ]);

            return Response::successResponse('Bank account added successfully', ['account' => $account]);

        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Get Saved Bank Accounts
     */
    public function getBankAccounts() {
        $accounts = BushaPaymentDetail::where('user_id', auth()->id())->get();
        return Response::successResponse('Bank accounts fetched', ['accounts' => $accounts]);
    }

    /**
     * Delete Bank Account
     */
    public function deleteBankAccount($id) {
        $account = BushaPaymentDetail::where('id', $id)->where('user_id', auth()->id())->first();
        if (!$account) {
            return Response::errorResponse('Bank account not found');
        }
        $account->delete();
        return Response::successResponse('Bank account deleted successfully');
    }
}
