<?php

namespace App\Http\Controllers\Api\Savings;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\SafeLock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\UserWallet;
use App\Models\SavingsTransaction;
use App\Models\SavingsPlan;
use Illuminate\Support\Carbon;

class SafeLockController extends Controller
{
    /**
     * List user's Safe Locks.
     */
    public function index()
    {
        $locks = SafeLock::where('user_id', auth()->id())->get();
        return Response::success(['safe_locks' => $locks]);
    }

    /**
     * List available Savings Plans (Fixed Rates).
     */
    public function plans()
    {
        $plans = SavingsPlan::where('status', true)->get();
        return Response::success(['plans' => $plans]);
    }

    /**
     * Create a new Safe Lock.
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'plan_id' => 'required|exists:savings_plans,id',
            'title' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        $plan = SavingsPlan::find($request->plan_id);
        
        if ($request->amount < $plan->min_amount) {
            return Response::error(['Amount is less than minimum for this plan']);
        }

        if ($plan->max_amount && $request->amount > $plan->max_amount) {
            return Response::error(['Amount exceeds maximum for this plan']);
        }

        // 1. Get NGN Wallet
        $user = auth()->user();
        $wallet = UserWallet::where('user_id', $user->id)->where('currency_code', 'NGN')->first();
        if (!$wallet) {
            return Response::error(['NGN Wallet not found']);
        }

        if ($wallet->balance < $request->amount) {
            return Response::error(['Insufficient wallet balance']);
        }

        $lockDate = now();
        $maturityDate = now()->addDays($plan->duration_days);

        // 2. Transact
        $wallet->balance -= $request->amount;
        $wallet->save();
        
        $lock = SafeLock::create([
            'user_id' => $user->id,
            'title' => $request->title ?? $plan->name,
            'amount' => $request->amount,
            'interest_rate' => $plan->interest_rate,
            'interest_accrued' => 0,
            'lock_date' => $lockDate,
            'maturity_date' => $maturityDate,
            'status' => 'active',
        ]);

        // 3. Log Transaction
        SavingsTransaction::create([
            'user_id' => $user->id,
            'savingsable_id' => $lock->id,
            'savingsable_type' => SafeLock::class,
            'amount' => $request->amount,
            'balance_after' => $lock->amount,
            'type' => 'deposit',
            'status' => 'success',
            'source' => 'wallet',
            'narration' => 'SafeLock Creation: ' . $lock->title
        ]);

        return Response::success(['message' => 'SafeLock created successfully', 'data' => $lock]);
    }

    /**
     * Break a Safe Lock early.
     */
    public function break(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lock_id' => 'required|exists:safe_locks,id',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        $lock = SafeLock::where('user_id', auth()->id())->find($request->lock_id);

        if (!$lock) {
            return Response::error(['SafeLock not found']);
        }

        if ($lock->status !== 'active') {
            return Response::error(['SafeLock is not active']);
        }

        // 1. Get NGN Wallet
        $user = auth()->user();
        $wallet = UserWallet::where('user_id', $user->id)->where('currency_code', 'NGN')->first();
        if (!$wallet) {
            return Response::error(['NGN Wallet not found']);
        }

        // Penalty Logic: Forfeit all interest, return only principal
        // 2. Transact
        $wallet->balance += $lock->amount;
        $wallet->save();

        $lock->status = 'broken';
        $lock->save();

        // 3. Log Transaction
        SavingsTransaction::create([
            'user_id' => $user->id,
            'savingsable_id' => $lock->id,
            'savingsable_type' => SafeLock::class,
            'amount' => $lock->amount,
            'balance_after' => 0,
            'type' => 'withdrawal',
            'status' => 'success',
            'source' => 'safelock',
            'narration' => 'SafeLock Broken (Early Withdrawal)'
        ]);

        return Response::success(['message' => 'SafeLock broken successfully. Principal returned to wallet.']);
    }

    /**
     * Get Transaction History.
     */
    public function history()
    {
        $transactions = SavingsTransaction::where('user_id', auth()->id())
            ->where('savingsable_type', SafeLock::class)
            ->latest()
            ->get();

        return Response::success(['transactions' => $transactions]);
    }
}
