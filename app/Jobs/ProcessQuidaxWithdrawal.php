<?php

namespace App\Jobs;

use App\Models\Withdrawals;
use App\Services\QuidaxService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessQuidaxWithdrawal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $mainAccountData;
    protected $destinationData;
    protected $feeAmount;
    protected $totalAmount;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $mainAccountData, $destinationData, $feeAmount = 0, $totalAmount = 0)
    {
        $this->user = $user;
        $this->mainAccountData = $mainAccountData;
        $this->destinationData = $destinationData;
        $this->feeAmount = $feeAmount;
        $this->totalAmount = $totalAmount;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(QuidaxService $quidax)
    {
        Log::info("ProcessQuidaxWithdrawal: Starting withdrawal for user " . $this->user->id);

        // 1. Withdraw to the main account first.
        $mainAccountResponse = $quidax->create_withdrawal($this->user->quidax_id, $this->mainAccountData);
        Log::info("ProcessQuidaxWithdrawal: Main account withdrawal response", ['response' => $mainAccountResponse]);

        // Wait for 120 seconds for confirmation/propagation
        sleep(120);

        if ($mainAccountResponse && isset($mainAccountResponse['status']) && $mainAccountResponse['status'] == "success") {
            
            // 2. Withdraw to destination
            $response = $quidax->create_withdrawal('me', $this->destinationData);
            Log::info("ProcessQuidaxWithdrawal: Destination withdrawal response", ['response' => $response]);

            if ($response && isset($response['status']) && $response['status'] == "success") {
                // Determine transaction ID safely
                $txid = $response['data']['txid'] ?? $response['data']['id'] ?? 'unknown';

                Withdrawals::create([
                    'user_id' => $this->user->id,
                    'reference' => $response['data']['reference'] ?? null,
                    'type' => $response['data']['type'] ?? null,
                    'currency' => $response['data']['currency'] ?? null,
                    'amount' => $response['data']['amount'] ?? null, // Amount sent
                    'fee' => $this->feeAmount, // Recorded Fee (Platform markup included)
                    'total' => $this->totalAmount, // Total debited from user
                    'trans_id' => $txid,
                    'transaction_note' => $response['data']['transaction_note'] ?? null,
                    'recipient_data' => $response['data']['recipient'] ?? null,
                    'wallet' => $response['data']['wallet'] ?? null,
                    'user' => $response['data']['user'] ?? null,
                ]);
                
                Log::info("ProcessQuidaxWithdrawal: Withdrawal record created.");

            } else {
                // Reverse the main account withdrawal if destination fails
                Log::info("ProcessQuidaxWithdrawal: Destination failed. Reversing main account withdrawal.");
                
                // Reverse the amount sent to Main Account (which was totalAmount)
                $mainWithdrawalId = $mainAccountResponse['data']['id'] ?? null;
                if($mainWithdrawalId){
                    $reverseResponse = $quidax->cancel_withdrawal($this->user->quidax_id, $mainWithdrawalId);
                    Log::info("ProcessQuidaxWithdrawal: Reversal response", ['response' => $reverseResponse]);
                }
                
                // TODO: Refund User Local Wallet?
                // Logic for refunding local wallet should ideally happen here if we are strict, 
                // but usually reversals are manual or handled by support for safety.
                // For now, logging reversal attempt.
            }

        } else {
            Log::error("ProcessQuidaxWithdrawal: Main account withdrawal failed.", ['response' => $mainAccountResponse]);
            // TODO: Refund User Local Wallet since Main Withdrawal failed.
        }
    }
}
