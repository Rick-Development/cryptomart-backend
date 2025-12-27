<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\P2PUserStat;
use App\Models\User;
use App\Services\P2PRiskService;
use Illuminate\Http\Request;

class P2PRiskController extends Controller
{
    protected $riskService;

    public function __construct(P2PRiskService $riskService)
    {
        $this->riskService = $riskService;
    }

    /**
     * List users by risk level
     */
    public function index(Request $request)
    {
        $query = P2PUserStat::with('user:id,firstname,lastname,email,kyc_tier');

        if ($request->has('level')) {
            $query->where('risk_level', $request->level);
        }

        if ($request->has('min_score')) {
            $query->where('risk_score', '>=', $request->min_score);
        }

        $users = $query->orderBy('risk_score', 'desc')->paginate(20);

        return Response::successResponse('User risk stats fetched', ['users' => $users]);
    }

    /**
     * Manually recalculate risk for a user
     */
    public function recalculate($userId)
    {
        $user = User::findOrFail($userId);
        $stats = $this->riskService->updateRiskScore($user);

        return Response::successResponse('Risk score updated', ['stats' => $stats]);
    }

    /**
     * Flag user (Restrict P2P access manually)
     */
    public function flagUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        // Assuming there is an is_banned or suspended column, or we toggle KYC
        // For now, we'll just set risk level to high manually if needed or implement a specific P2P ban flag
        // Let's implement a 'p2p_banned' column in p2p_user_stats or users table.
        // For this task, we will just update the P2PUserStat to high risk manually.
        
        $stats = P2PUserStat::firstOrCreate(['user_id' => $userId]);
        $stats->risk_level = 'high';
        $stats->risk_score = 100; // Force max
        $stats->save();

        return Response::successResponse('User flagged as high risk', ['stats' => $stats]);
    }
}
