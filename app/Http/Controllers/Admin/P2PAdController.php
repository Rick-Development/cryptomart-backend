<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\P2PAd;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class P2PAdController extends Controller
{
    /**
     * List all ads with filters
     */
    public function index(Request $request)
    {
        $query = P2PAd::with(['user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $ads = $query->latest()->paginate(50);

        return Response::successResponse('Ads fetched', ['ads' => $ads]);
    }

    /**
     * Show ad details
     */
    public function show($id)
    {
        $ad = P2PAd::with(['user', 'orders'])->findOrFail($id);

        return Response::successResponse('Ad details', ['ad' => $ad]);
    }

    /**
     * Update ad (approve/reject/edit)
     */
    public function update(Request $request, $id)
    {
        $ad = P2PAd::findOrFail($id);

        if ($request->filled('status')) {
            $ad->status = $request->status;
        }

        $ad->save();

        return Response::successResponse('Ad updated', ['ad' => $ad]);
    }

    /**
     * Delete ad (soft delete)
     */
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $ad = P2PAd::findOrFail($id);

            // If sell ad, return reserved funds
            if ($ad->type === 'sell' && $ad->available_amount > 0) {
                $wallet = UserWallet::where('user_id', $ad->user_id)
                    ->where('currency', $ad->asset)
                    ->first();

                if ($wallet) {
                    $wallet->reserved -= $ad->available_amount;
                    $wallet->balance += $ad->available_amount;
                    $wallet->save();
                }
            }

            $ad->status = 'deleted';
            $ad->save();

            return Response::successResponse('Ad deleted');
        });
    }
}
