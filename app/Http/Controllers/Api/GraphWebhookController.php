<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GraphCustomer;
use App\Models\GraphTransaction;
use App\Models\GraphWallet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\KycVerification; // Assuming this tracks local KYC

class GraphWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // 1. Verify Signature (Security)
        $signature = $request->header('x-graph-signature');
        $payload = $request->getContent();
        $secret = config('graph.webhook_secret');

        if (!$this->verifySignature($payload, $signature, $secret)) {
            Log::warning('Graph Webhook: Invalid Signature');
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        Log::info("Graph Webhook Received: {$event}", ['data' => $data]);

        try {
            switch ($event) {
                case 'deposit.successful':
                    $this->handleDeposit($data);
                    break;

                case 'payout.updated':
                    $this->handlePayoutUpdate($data);
                    break;

                case 'kyc.verification.successful':
                case 'kyc.verification.failed':
                    $this->handleKycUpdate($event, $data);
                    break;

                default:
                    Log::info("Graph Webhook: Unhandled event type {$event}");
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error("Graph Webhook Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function verifySignature($payload, $signature, $secret)
    {
        // If secret is not set, skip verification (User needs to set this!)
        if (empty($secret)) {
            Log::warning('Graph Webhook: Secret not set in config, skipping signature check.');
            return true; 
        }

        $computedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($computedSignature, $signature);
    }

    private function handleDeposit($data)
    {
        // Data usually contains: id, amount, currency, virtual_account_id, etc.
        $walletId = $data['virtual_account_id'];
        $amount = $data['amount'];
        $currency = $data['currency'];
        $txRef = $data['reference'] ?? $data['id'];

        // 1. Find the wallet
        $wallet = GraphWallet::where('wallet_id', $walletId)->first();
        if (!$wallet) {
            Log::error("Graph Webhook: Wallet not found for ID {$walletId}");
            return;
        }

        // 2. Check if transaction already exists to prevent duplicate credit
        if (GraphTransaction::where('transaction_id', $data['id'])->exists()) {
            Log::info("Graph Webhook: Duplicate deposit event for {$data['id']}");
            return;
        }

        // 3. Update Balance
        $wallet->balance += $amount;
        $wallet->save();

        // 4. Record Transaction
        GraphTransaction::create([
            'user_id' => $wallet->user_id,
            'graph_wallet_id' => $wallet->id,
            'transaction_id' => $data['id'],
            'type' => 'deposit',
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'successful',
            'reference' => $txRef,
            'description' => "Deposit of {$amount} {$currency}",
            'metadata' => $data,
        ]);

        Log::info("Graph Webhook: Deposit processed for wallet {$walletId}");
    }

    private function handlePayoutUpdate($data)
    {
        $transaction = GraphTransaction::where('transaction_id', $data['id'])->first();

        if ($transaction) {
            $transaction->update([
                'status' => $data['status'],
                'metadata' => array_merge($transaction->metadata ?? [], ['updated_at' => now(), 'update_data' => $data])
            ]);
            Log::info("Graph Webhook: Payout status updated to {$data['status']} for {$data['id']}");
        }
    }

    private function handleKycUpdate($event, $data)
    {
        $personId = $data['person_id'] ?? $data['id'];
        $customer = GraphCustomer::where('graph_id', $personId)->first();

        if (!$customer) {
            Log::error("Graph Webhook: Customer not found for Graph ID {$personId}");
            return;
        }

        $status = ($event === 'kyc.verification.successful') ? 'verified' : 'failed';
        
        // Update GraphCustomer status
        $customer->update([
            'kyc_status' => $status,
            'data' => array_merge($customer->data ?? [], ['kyc_update' => $data])
        ]);

        // Integrate with Main User KYC (Tier 2/3)
        // If Graph verification is successful, we can auto-approve specific tiers if logic permits
        if ($status === 'verified') {
            $user = $customer->user;
            
            // Example: If user was pending Tier 2, mark as verified
            // This logic depends on the user's specific Tier rules. 
            // For now, we log it. User can further customize this integration.
            Log::info("Graph Webhook: KYC Verified for User {$user->id}. Ready for Tier upgrade.");
            
            // Uncomment to auto-verify user's main KYC verified flag:
            // $user->update(['kyc_verified' => true]);
        }
    }
}
