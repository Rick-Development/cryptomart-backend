<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MerchantApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchantApplicationController extends Controller
{
    /**
     * List all applications
     */
    public function index(Request $request)
    {
        $page_title = "Merchant Applications";
        $query = MerchantApplication::with(['user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->latest()->paginate(20);

        return view('admin.sections.p2p.merchant.index', compact('page_title', 'applications'));
    }

    /**
     * Show application details
     */
    public function show($id)
    {
        $page_title = "Application Details";
        $application = MerchantApplication::with(['user', 'reviewer'])->findOrFail($id);

        return view('admin.sections.p2p.merchant.details', compact('page_title', 'application'));
    }

    /**
     * Approve application
     */
    public function approve(Request $request, $id)
    {
        return DB::transaction(function () use ($id, $request) {
            $application = MerchantApplication::findOrFail($id);
            $user = User::findOrFail($application->user_id);

            if ($application->status !== 'pending') {
                return back()->with(['error' => ['Application is not pending']]);
            }

            // Update Application
            $application->update([
                'status' => 'approved',
                'reviewed_by' => auth()->guard('admin')->id(),
                'reviewed_at' => now(),
                'admin_notes' => $request->admin_notes,
            ]);

            // Update User
            $user->update([
                'merchant_status' => 'approved',
                'merchant_approved_at' => now(),
                'kyc_tier' => max($user->kyc_tier, 3), // Upgrade to Level 3 (Merchant) if lower
            ]);

            // Optional: Send Notification to User

            return back()->with(['success' => ['Merchant application approved successfully']]);
        });
    }

    /**
     * Reject application
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        return DB::transaction(function () use ($id, $request) {
            $application = MerchantApplication::findOrFail($id);
            $user = User::findOrFail($application->user_id);

            if ($application->status !== 'pending') {
                return back()->with(['error' => ['Application is not pending']]);
            }

            // Update Application
            $application->update([
                'status' => 'rejected',
                'reviewed_by' => auth()->guard('admin')->id(),
                'reviewed_at' => now(),
                'admin_notes' => $request->reason,
            ]);

            // Update User
            $user->update([
                'merchant_status' => 'rejected',
            ]);

            // Optional: Send Notification to User

        });
    }

    /**
     * Merchant Settings Page
     */
    public function settings()
    {
        $page_title = "Merchant Settings";
        $setting = \App\Models\AdminSetting::where('setting_key', 'merchant_min_usdt')->first();

        return view('admin.sections.p2p.merchant.settings', compact('page_title', 'setting'));
    }

    /**
     * Update Merchant Settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'merchant_min_usdt' => 'required|numeric|min:0',
        ]);

        \App\Models\AdminSetting::updateOrCreate(
            ['setting_key' => 'merchant_min_usdt'],
            [
                'setting_value' => $request->merchant_min_usdt,
                'description'   => 'Minimum USDT required for merchant application'
            ]
        );

        return back()->with(['success' => ['Settings updated successfully']]);
    }
}
