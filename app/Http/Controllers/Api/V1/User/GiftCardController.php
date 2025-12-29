<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\GiftCardCategory;
use App\Models\GiftCardCountry;
use App\Models\GiftCardTransaction;
use App\Models\UserWallet;
use App\Services\ReloadlyService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class GiftCardController extends Controller
{
    protected $reloadly;

    public function __construct(ReloadlyService $reloadly)
    {
        $this->reloadly = $reloadly;
    }

    /**
     * Get discovery data (categories, countries, featured discounts)
     */
    public function discovery()
    {
        try {
            $categories = GiftCardCategory::where('status', true)->get();
            $countries = GiftCardCountry::where('status', true)->get();
            
            // Featured discounts (Get from API livedata)
            $discountsResponse = $this->reloadly->getDiscounts(['size' => 10]);
            $discounts = $discountsResponse['content'] ?? $discountsResponse;

            return Response::successResponse('Discovery data fetched', [
                'categories' => $categories,
                'countries' => $countries,
                'featured_discounts' => $discounts
            ]);
        } catch (Exception $e) {
            return Response::errorResponse('Failed to fetch discovery data: ' . $e->getMessage());
        }
    }

    /**
     * List products with filters
     */
    public function products(Request $request)
    {
        try {
            $filters = $request->only(['countryCode', 'productCategoryId', 'productName', 'page', 'size']);
            $products = $this->reloadly->getProducts($filters);

            return Response::successResponse('Products fetched', ['products' => $products]);
        } catch (Exception $e) {
            return Response::errorResponse('Failed to fetch products: ' . $e->getMessage());
        }
    }

    /**
     * Product details + instructions
     */
    public function productDetails($id)
    {
        try {
            $product = $this->reloadly->getProductById($id);
            $instructions = $this->reloadly->getRedeemInstructionsByProduct($id);

            return Response::successResponse('Product details', [
                'product' => $product,
                'instructions' => $instructions
            ]);
        } catch (Exception $e) {
            return Response::errorResponse('Failed to fetch product details: ' . $e->getMessage());
        }
    }

    /**
     * Fetch FX rate preview
     */
    public function fxRate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currencyCode' => 'required|string|size:3',
            'amount' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return Response::errorResponse('Validation Error', $validator->errors()->all());
        }

        try {
            $rate = $this->reloadly->getFxRate($request->currencyCode, $request->amount);
            
            // In a real scenario, markups would be fetched from BasicSettings or a GiftCardSetting model
            $markup = 0.05; // Default 5% markup for crypto/giftcard platform risk
            
            $rate['platform_markup_percentage'] = $markup * 100;
            $rate['estimated_total_with_markup'] = $rate['senderAmount'] * (1 + $markup);
            
            return Response::successResponse('FX Rate preview', $rate);
        } catch (Exception $e) {
            return Response::errorResponse('Failed to fetch FX rate: ' . $e->getMessage());
        }
    }

    /**
     * Sycn categories and countries from Reloadly (Internal/Admin use)
     */
    public function syncMetadata()
    {
        try {
            DB::transaction(function() {
                // Sync Categories
                $categories = $this->reloadly->getCategories();
                foreach($categories as $cat) {
                    GiftCardCategory::updateOrCreate(
                        ['id' => $cat['id']],
                        ['name' => $cat['name']]
                    );
                }

                // Sync Countries
                $countries = $this->reloadly->getCountries();
                foreach($countries as $country) {
                    GiftCardCountry::updateOrCreate(
                        ['iso_name' => $country['isoName']],
                        [
                            'name' => $country['name'],
                            'currency_code' => $country['currencyCode'],
                            'flag_url' => $country['flag']
                        ]
                    );
                }
            });

            return Response::successResponse('Metadata synced successfully');
        } catch (Exception $e) {
            return Response::errorResponse('Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Place a gift card order
     */
    public function storeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'wallet_id' => 'required|exists:user_wallets,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0.01',
            'sender_name' => 'required|string|max:100',
            'recipient_email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return Response::errorResponse('Validation Error', $validator->errors()->all());
        }

        $user = auth()->user();
        $wallet = UserWallet::where('id', $request->wallet_id)->where('user_id', $user->id)->firstOrFail();

        try {
            // 1. Fetch Product Details to verify price
            $product = $this->reloadly->getProductById($request->product_id);
            
            // Validate Denomination
            if ($product['denominationType'] === 'FIXED') {
                if (!in_array($request->unit_price, $product['fixedRecipientDenominations'])) {
                    return Response::errorResponse("Invalid denomination. Allowed: " . implode(', ', $product['fixedRecipientDenominations']) . " " . $product['recipientCurrencyCode']);
                }
            } else {
                if ($request->unit_price < $product['minRecipientDenomination'] || $request->unit_price > $product['maxRecipientDenomination']) {
                    return Response::errorResponse("Amount out of range. Allowed: " . $product['minRecipientDenomination'] . " - " . $product['maxRecipientDenomination'] . " " . $product['recipientCurrencyCode']);
                }
            }

            $markup = 0.05; // 5% platform fee
            $totalProductPrice = $request->unit_price * $request->quantity;

            // Calculate exact debit amount in Wallet Currency
            if ($wallet->currency_code === $product['recipientCurrencyCode']) {
                // Direct payment (e.g. USD wallet for USD product)
                $totalDebitAmount = $totalProductPrice * (1 + $markup);
            } else {
                // Cross-currency payment (e.g. NGN wallet for USD product)
                // Reloadly provides the rate from recipientCode -> our base senderCurrency (NGN)
                $fx = $this->reloadly->getFxRate($product['recipientCurrencyCode'], (float)$totalProductPrice);
                
                if ($wallet->currency_code !== $fx['senderCurrency']) {
                     return Response::errorResponse("Currency mismatch. This product requires payment in " . $product['recipientCurrencyCode'] . " or " . $fx['senderCurrency']);
                }
                
                $totalDebitAmount = $fx['senderAmount'] * (1 + $markup);
            }

            // check wallet balance
            if (bccomp($wallet->balance, (string)$totalDebitAmount, 18) < 0) {
                return Response::errorResponse("Insufficient wallet balance. Required: " . number_format($totalDebitAmount, 2) . " " . $wallet->currency_code);
            }

            // 2. Begin Transaction & Debit Wallet
            return DB::transaction(function () use ($request, $user, $wallet, $product, $totalDebitAmount) {
                
                $customIdentifier = 'GC' . time() . str_pad($user->id, 5, '0', STR_PAD_LEFT);

                // Create Transaction record (PENDING)
                $transaction = GiftCardTransaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'custom_identifier' => $customIdentifier,
                    'status' => 'PENDING',
                    'amount' => $totalDebitAmount,
                    'currency' => $wallet->currency_code,
                    'fee' => $totalDebitAmount - ($request->unit_price * $request->quantity),
                    'product_id' => $product['productId'],
                    'product_name' => $product['productName'],
                    'quantity' => $request->quantity,
                    'unit_price' => $request->unit_price,
                    'product_currency' => $product['recipientCurrencyCode'],
                    'recipient_email' => $request->recipient_email,
                    'meta' => ['product_details' => $product]
                ]);

                // Debit User
                WalletService::debit($wallet->id, (string)$totalDebitAmount, 'giftcard:order:' . $customIdentifier);

                try {
                    // 3. Place Order with Reloadly
                    $orderPayload = [
                        'productId' => $product['productId'],
                        'quantity' => $request->quantity,
                        'unitPrice' => $request->unit_price,
                        'senderName' => $request->sender_name,
                        'recipientEmail' => $request->recipient_email,
                        'customIdentifier' => $customIdentifier,
                    ];

                    $reloadlyOrder = $this->reloadly->placeOrder($orderPayload);

                    if (isset($reloadlyOrder['transactionId'])) {
                        // 4. Update Success
                        $transaction->update([
                            'reloadly_transaction_id' => $reloadlyOrder['transactionId'],
                            'status' => 'SUCCESSFUL',
                            'meta' => array_merge($transaction->meta, ['order_response' => $reloadlyOrder])
                        ]);

                        // 5. Fetch Redeem Codes immediately
                        try {
                            $codes = $this->reloadly->getRedeemCode($reloadlyOrder['transactionId']);
                            if ($codes) {
                                $transaction->update([
                                    'card_number' => $codes['cardNumber'] ?? null,
                                    'pin_code' => $codes['pinCode'] ?? null,
                                    'redemption_url' => $codes['redemptionUrl'] ?? null,
                                ]);
                            }
                        } catch (Exception $e) {
                            \Log::error("Failed to fetch gift card codes for TX: " . $reloadlyOrder['transactionId']);
                        }

                        return Response::success('Gift card purchased successfully', $transaction->load('user'));
                    }

                    throw new Exception("Reloadly Order Failed: Product likely out of stock or price mismatch.");

                } catch (Exception $e) {
                    // 6. Handle Failure & Refund
                    $transaction->update(['status' => 'FAILED', 'meta' => array_merge($transaction->meta, ['error' => $e->getMessage()])]);
                    
                    // Refund 
                    WalletService::credit($wallet->id, (string)$totalDebitAmount, 'giftcard:refund:' . $customIdentifier);
                    
                    return Response::errorResponse('Purchase failed: ' . $e->getMessage());
                }
            });

        } catch (Exception $e) {
            return Response::errorResponse('Process failed: ' . $e->getMessage());
        }
    }
}
