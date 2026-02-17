<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\MerchantApplication;
use App\Models\AdminSetting;
use App\Services\QuidaxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class MerchantApplicationController extends Controller
{
    protected $quidaxService;

    public function __construct(QuidaxService $quidaxService)
    {
        $this->quidaxService = $quidaxService;
    }

    /**
     * Check if user is eligible to apply for merchant status
     */
    public function checkEligibility()
    {
        $user = auth()->user();
        
        // 1. Get Minimum USDT Requirement
        $minUsdt = AdminSetting::where('setting_key', 'merchant_min_usdt')->value('setting_value') ?? 100;
        
        // 2. Check Quidax USDT Balance
        $usdtBalance = 0;
        try {
            // Quidax uses 'usdt' (lowercase)
            $response = $this->quidaxService->fetchUserWallet($user->quidax_id, 'usdt');
            
            if (isset($response['data']) && isset($response['data']['balance'])) {
                $usdtBalance = (float) $response['data']['balance'];
            }
        } catch (Exception $e) {
            return Response::errorResponse('Failed to fetch Quidax balance: ' . $e->getMessage());
        }

        $isEligible = $usdtBalance >= $minUsdt;
        
        $hasApplication = MerchantApplication::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        // If not eligible return error with data, otherwise success
        if (!$isEligible) {
             return Response::errorResponse("Insufficient USDT balance", [
                'eligible' => false,
                'quidax_usdt_balance' => $usdtBalance,
                'min_usdt_required' => (float)$minUsdt,
                'shortfall' => $minUsdt - $usdtBalance
            ], 400);
        }

        return Response::successResponse('You are eligible to apply for merchant status', [
            'eligible' => true,
            'quidax_usdt_balance' => $usdtBalance,
            'min_usdt_required' => (float)$minUsdt,
            'has_active_application' => $hasApplication
        ]);
    }

    /**
     * Submit merchant application
     */
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'whatsapp' => 'nullable|string|max:20',
            'business_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return Response::errorResponse('Validation Error', $validator->errors()->all());
        }

        $user = auth()->user();

        // 1. Check for existing application
        $existingApp = MerchantApplication::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingApp) {
            $statusMsg = $existingApp->status === 'approved' ? 'already an approved merchant' : 'already have a pending application';
            return Response::errorResponse("You {$statusMsg}.", [
                'application_id' => $existingApp->id,
                'status' => $existingApp->status
            ], 400);
        }

        // 2. Verify Balance Again (Security)
        $minUsdt = AdminSetting::where('setting_key', 'merchant_min_usdt')->value('setting_value') ?? 100;
        $usdtBalance = 0;
        
        try {
            $response = $this->quidaxService->fetchUserWallet($user->quidax_id, 'usdt');
            if (isset($response['data']) && isset($response['data']['balance'])) {
                $usdtBalance = (float) $response['data']['balance'];
            }
        } catch (Exception $e) {
            return Response::errorResponse('Failed to verify balance. Please try again.');
        }

        if ($usdtBalance < $minUsdt) {
            return Response::errorResponse("Insufficient USDT balance. Minimum required: {$minUsdt} USDT.", null, 400);
        }

        // 3. Create Application
        DB::beginTransaction();
        try {
            // Use user details from profile
            $userPhone = $user->full_mobile ?? $user->mobile;
            
            $application = MerchantApplication::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'phone' => $userPhone,
                'whatsapp' => $request->whatsapp ?? $userPhone,
                'business_name' => $request->business_name,
                'quidax_usdt_balance' => $usdtBalance,
                'min_usdt_required' => $minUsdt,
                'balance_verified_at' => now(),
                'status' => 'pending',
            ]);

            // Update user status
            $user->merchant_status = 'pending';
            $user->save();

            DB::commit();

            return Response::successResponse('Merchant application submitted successfully. We will contact you for verification.', [
                'application_id' => $application->id,
                'status' => 'pending',
                'submitted_at' => $application->created_at,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return Response::errorResponse('Failed to submit application: ' . $e->getMessage());
        }
    }

    /**
     * Check application status
     */
    public function status()
    {
        $user = auth()->user();
        
        $application = MerchantApplication::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$application) {
            return Response::successResponse('Merchant status', [
                'has_application' => false,
                'status' => 'none',
                'can_apply' => true,
                'message' => 'You have not applied for merchant status yet.'
            ]);
        }

        $data = [
            'has_application' => true,
            'status' => $application->status,
            'submitted_at' => $application->created_at,
            'admin_notes' => $application->admin_notes,
        ];

        if ($application->status === 'approved') {
            $data['approved_at'] = $application->reviewed_at;
            $data['message'] = 'Congratulations! You are a verified merchant.';
        } elseif ($application->status === 'rejected') {
            $data['rejected_at'] = $application->reviewed_at;
            $data['can_reapply'] = true; // Logic for reapply delay could go here
            $data['message'] = 'Your application was rejected. See admin notes for details.';
        } else {
            $data['message'] = 'Your application is under pending review. We will contact you.';
        }

        return Response::successResponse('Merchant application status', $data);
    }
}
