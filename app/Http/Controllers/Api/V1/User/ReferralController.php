<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use App\Http\Helpers\Response;
use Illuminate\Http\Request;
use Exception;

class ReferralController extends Controller
{
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    /**
     * Get my referral info and statistics
     * GET /api/user/referral/info
     */
    public function getInfo()
    {
        try {
            $user = auth()->user();
            
            // Generate referral code if user doesn't have one
            if (!$user->referral_code) {
                $this->referralService->generateReferralCode($user);
                $user->refresh();
            }

            $stats = $this->referralService->getStatistics($user);
            
            return Response::successResponse('Referral information retrieved successfully', $stats);
        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Get list of my referrals
     * GET /api/user/referral/list
     */
    public function getList(Request $request)
    {
        try {
            $user = auth()->user();
            $perPage = $request->input('per_page', 20);
            
            $referrals = $this->referralService->getReferralList($user, $perPage);
            
            return Response::successResponse('Referral list retrieved successfully', $referrals);
        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Get my referral earnings
     * GET /api/user/referral/earnings
     */
    public function getEarnings(Request $request)
    {
        try {
            $user = auth()->user();
            $perPage = $request->input('per_page', 20);
            
            $earnings = $this->referralService->getEarnings($user, $perPage);
            
            return Response::successResponse('Referral earnings retrieved successfully', $earnings);
        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Validate a referral code (public endpoint)
     * POST /api/referral/validate
     */
    public function validateCode(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string|max:20',
        ]);

        try {
            $referrer = $this->referralService->validateReferralCode($request->referral_code);
            
            if (!$referrer) {
                return Response::errorResponse('Invalid referral code');
            }

            return Response::successResponse('Referral code is valid', [
                'valid' => true,
                'referrer_username' => substr($referrer->username, 0, 3) . '***',
            ]);
        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }
}
