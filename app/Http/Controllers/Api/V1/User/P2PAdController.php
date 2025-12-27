<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\P2PAd;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class P2PAdController extends Controller
{
    /**
     * Browse all active ads with filters
     */
    public function index(Request $request)
    {
        $query = P2PAd::with(['user', 'user.p2pUserStat'])
            ->where('status', 'online');

        // Filters
        if ($request->filled('asset')) {
            $query->where('asset', $request->asset);
        }

        if ($request->filled('fiat')) {
            $query->where('fiat', $request->fiat);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('payment_method_id')) {
            $query->whereJsonContains('payment_method_ids', (int)$request->payment_method_id);
        }

        if ($request->filled('amount')) {
            $amount = $request->amount;
            $query->where('min_limit', '<=', $amount)
                  ->where('max_limit', '>=', $amount)
                  ->where('available_amount', '>=', $amount);
        }

        $ads = $query->latest()->paginate(20);

        return Response::successResponse('Ads fetched successfully', ['ads' => $ads]);
    }

    /**
     * Create new ad (requires KYC Level 2+)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:buy,sell',
            'asset' => 'required|string|max:16',
            'fiat' => 'required|string|max:16',
            'price_type' => 'required|in:fixed,floating',
            'price' => 'required|numeric|min:0',
            'margin' => 'nullable|numeric',
            'total_amount' => 'required|numeric|min:0',
            'min_limit' => 'required|numeric|min:0',
            'max_limit' => 'required|numeric|min:0',
            'payment_method_ids' => 'required|array',
            'payment_method_ids.*' => 'exists:p2p_payment_methods,id',
            'terms' => 'nullable|string',
            'auto_reply' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:5|max:60',
        ]);

        if ($validator->fails()) {
            return Response::errorResponse('Validation Error', $validator->errors()->all());
        }

        $user = auth()->user();

        // Check KYC Level
        if ($user->kyc_tier < 2) {
            return Response::errorResponse('KYC Level 2 required to create ads', null, 403);
        }

        // For sell ads, lock crypto in escrow
        if ($request->type === 'sell') {
            $wallet = UserWallet::where('user_id', $user->id)
                ->where('currency', $request->asset)
                ->first();

            if (!$wallet || $wallet->balance < $request->total_amount) {
                return Response::errorResponse('Insufficient balance to create sell ad');
            }

            // Lock funds
            DB::transaction(function () use ($wallet, $request) {
                $wallet->balance -= $request->total_amount;
                $wallet->reserved += $request->total_amount;
                $wallet->save();
            });
        }

        $ad = P2PAd::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'asset' => $request->asset,
            'fiat' => $request->fiat,
            'price_type' => $request->price_type,
            'price' => $request->price,
            'margin' => $request->margin,
            'total_amount' => $request->total_amount,
            'available_amount' => $request->total_amount,
            'min_limit' => $request->min_limit,
            'max_limit' => $request->max_limit,
            'payment_method_ids' => $request->payment_method_ids,
            'terms' => $request->terms,
            'auto_reply' => $request->auto_reply,
            'time_limit' => $request->time_limit ?? 15,
            'status' => 'offline', // Admin approval required
        ]);

        return Response::successResponse('Ad created successfully. Pending approval.', ['ad' => $ad], 201);
    }

    /**
     * Toggle ad online/offline
     */
    public function toggle($id)
    {
        $ad = P2PAd::where('user_id', auth()->id())->findOrFail($id);

        $newStatus = $ad->status === 'online' ? 'offline' : 'online';
        $ad->status = $newStatus;
        $ad->save();

        return Response::successResponse("Ad is now {$newStatus}", ['ad' => $ad]);
    }

    /**
     * Get user's own ads
     */
    public function myAds()
    {
        $ads = P2PAd::where('user_id', auth()->id())
            ->latest()
            ->get();

        return Response::successResponse('Your ads fetched', ['ads' => $ads]);
    }

    /**
     * Show single ad details
     */
    public function show($id)
    {
        $ad = P2PAd::with(['user', 'user.p2pUserStat'])->findOrFail($id);

        return Response::successResponse('Ad details', ['ad' => $ad]);
    }
}
