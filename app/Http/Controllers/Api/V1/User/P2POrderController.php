<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\P2PAd;
use App\Models\P2POrder;
use App\Models\P2PChat;
use App\Models\UserWallet;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class P2POrderController extends Controller
{
    /**
     * Create order from ad
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ad_id' => 'required|exists:p2p_ads,id',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return Response::errorResponse('Validation Error', $validator->errors()->all());
        }

        return DB::transaction(function () use ($request) {
            $ad = P2PAd::where('id', $request->ad_id)
                ->where('status', 'online')
                ->lockForUpdate()
                ->firstOrFail();

            // Validate amount limits
            if ($request->amount < $ad->min_limit || $request->amount > $ad->max_limit) {
                return Response::errorResponse("Amount must be between {$ad->min_limit} and {$ad->max_limit}");
            }

            // Check availability
            if ($ad->available_amount < $request->amount) {
                return Response::errorResponse('Insufficient ad availability');
            }

            // Cannot trade with self
            if ($ad->user_id === auth()->id()) {
                return Response::errorResponse('Cannot trade with your own ad');
            }

            // Calculate total
            $total = bcmul($request->amount, $ad->price, 2);

            // Lock funds in escrow (for sell ads, buyer pays fiat, seller already locked crypto)
            if ($ad->type === 'buy') {
                // Buyer selling crypto to ad owner
                $wallet = UserWallet::where('user_id', auth()->id())
                    ->where('currency', $ad->asset)
                    ->first();

                if (!$wallet || $wallet->balance < $request->amount) {
                    return Response::errorResponse('Insufficient balance');
                }

                WalletService::debitToReserve($wallet->id, (string)$request->amount, 'p2p:order:' . time(), []);
            }

            // Reduce ad availability
            $ad->available_amount -= $request->amount;
            $ad->save();

            // Create order
            $order = P2POrder::create([
                'ad_id' => $ad->id,
                'maker_id' => $ad->user_id,
                'taker_id' => auth()->id(),
                'type' => $ad->type,
                'asset' => $ad->asset,
                'quote_currency' => $ad->fiat,
                'amount' => $request->amount,
                'price' => $ad->price,
                'locked_price' => $ad->price,
                'total' => $total,
                'escrow_enabled' => true,
                'status' => 'accepted',
                'payment_deadline' => Carbon::now()->addMinutes($ad->time_limit),
            ]);

            return Response::successResponse('Order created successfully', ['order' => $order], 201);
        });
    }

    /**
     * Buyer marks payment sent
     */
    public function markPaid($uid)
    {
        $order = P2POrder::where('id', $uid)
            ->where('taker_id', auth()->id())
            ->firstOrFail();

        if ($order->status !== 'accepted') {
            return Response::errorResponse('Invalid order status');
        }

        $order->status = 'paid';
        $order->save();

        event(new \App\Events\P2POrderStatusUpdated($order));

        return Response::successResponse('Payment marked as sent. Waiting for seller confirmation.', ['order' => $order]);
    }

    /**
     * Seller releases crypto
     */
    public function release($uid)
    {
        return DB::transaction(function () use ($uid) {
            $order = P2POrder::where('id', $uid)->lockForUpdate()->firstOrFail();

            // Only maker can release
            if ($order->maker_id !== auth()->id()) {
                return Response::errorResponse('Unauthorized', null, 403);
            }

            if ($order->status !== 'paid' && $order->status !== 'funded') {
                return Response::errorResponse('Order not ready for release');
            }

            // Determine wallets based on order type
            if ($order->type === 'sell') {
                // Maker selling crypto, release from maker's reserved to taker
                $fromWallet = UserWallet::where('user_id', $order->maker_id)
                    ->where('currency', $order->asset)
                    ->firstOrFail();
                $toWallet = UserWallet::where('user_id', $order->taker_id)
                    ->where('currency', $order->asset)
                    ->firstOrFail();
                $amount = (string)$order->amount;
            } else {
                // Maker buying crypto, release from taker's reserved to maker
                $fromWallet = UserWallet::where('user_id', $order->taker_id)
                    ->where('currency', $order->asset)
                    ->firstOrFail();
                $toWallet = UserWallet::where('user_id', $order->maker_id)
                    ->where('currency', $order->asset)
                    ->firstOrFail();
                $amount = (string)$order->amount;
            }

            WalletService::releaseReservedTo($fromWallet->id, $toWallet->id, $amount, 'p2p:release:' . $order->id, ['order_id' => $order->id]);

            $order->status = 'completed';
            $order->save();

            event(new \App\Events\P2POrderStatusUpdated($order));

            return Response::successResponse('Crypto released successfully', ['order' => $order]);
        });
    }

    /**
     * Raise dispute/appeal
     */
    public function appeal(Request $request, $uid)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
            'evidence' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return Response::errorResponse('Validation Error', $validator->errors()->all());
        }

        $order = P2POrder::findOrFail($uid);

        // Check if user is part of trade
        if ($order->maker_id !== auth()->id() && $order->taker_id !== auth()->id()) {
            return Response::errorResponse('Unauthorized', null, 403);
        }

        if (!in_array($order->status, ['paid', 'accepted', 'funded'])) {
            return Response::errorResponse('Order cannot be disputed in current state');
        }

        $order->appeal_status = 'pending';
        $order->appeal_reason = $request->reason;
        $order->evidence = $request->evidence ?? [];
        $order->save();

        event(new \App\Events\P2POrderStatusUpdated($order));

        return Response::successResponse('Dispute raised successfully. Admin will review.', ['order' => $order]);
    }

    /**
     * Get order chat messages
     */
    public function chat($uid)
    {
        $order = P2POrder::findOrFail($uid);

        // Check if user is part of trade
        if ($order->maker_id !== auth()->id() && $order->taker_id !== auth()->id()) {
            return Response::errorResponse('Unauthorized', null, 403);
        }

        $messages = P2PChat::where('order_id', $order->id)
            ->with('sender:id,firstname,lastname,username')
            ->orderBy('created_at', 'asc')
            ->get();

        return Response::successResponse('Chat messages', ['messages' => $messages]);
    }

    /**
     * Send chat message
     */
    public function sendMessage(Request $request, $uid)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'attachment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Response::errorResponse('Validation Error', $validator->errors()->all());
        }

        $order = P2POrder::findOrFail($uid);

        // Check if user is part of trade
        if ($order->maker_id !== auth()->id() && $order->taker_id !== auth()->id()) {
            return Response::errorResponse('Unauthorized', null, 403);
        }

        $message = P2PChat::create([
            'order_id' => $order->id,
            'sender_id' => auth()->id(),
            'message' => $request->message,
            'attachment' => $request->attachment,
        ]);

        event(new \App\Events\P2PMessageSent($message));

        return Response::successResponse('Message sent', ['message' => $message], 201);
    }

    /**
     * Get user's orders
     */
    public function myOrders()
    {
        $orders = P2POrder::where(function ($q) {
                $q->where('maker_id', auth()->id())
                  ->orWhere('taker_id', auth()->id());
            })
            ->with(['ad', 'maker:id,firstname,lastname,username', 'taker:id,firstname,lastname,username'])
            ->latest()
            ->get();

        return Response::successResponse('Your orders', ['orders' => $orders]);
    }
}
