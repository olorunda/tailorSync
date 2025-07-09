<?php

namespace App\Jobs;

use App\Models\BusinessDetail;
use App\Models\SubscriptionHistory;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class CancelSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The user whose subscription is being cancelled.
     *
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $businessDetail = $this->user->businessDetail;
        $subscriptionCode = $businessDetail->subscription_code;

        Log::info('Executing scheduled subscription cancellation for user: ' . $this->user->id);

        // If this is a Paystack recurring subscription, cancel it with Paystack
        if ($subscriptionCode && $businessDetail->subscription_payment_method === 'paystack') {
            try {
                $paymentService = PaymentService::forSubscription($this->user);
                $result = $paymentService->cancelPaystackSubscription($subscriptionCode, $this->user->email);

                // Log the result for debugging
                Log::info('Paystack subscription cancellation result', $result);
            } catch (Exception $e) {
                // Log the error but continue with local cancellation
                Log::error('Error cancelling Paystack subscription: ' . $e->getMessage());
            }
        }

        // Set subscription to inactive
        $businessDetail->subscription_active = false;
        $saved = $businessDetail->save();

        if ($saved) {
            // Record subscription history
            SubscriptionHistory::create([
                'business_detail_id' => $businessDetail->id,
                'subscription_plan' => $businessDetail->subscription_plan,
                'subscription_start_date' => $businessDetail->subscription_start_date,
                'subscription_end_date' => $businessDetail->subscription_end_date,
                'subscription_active' => $businessDetail->subscription_active,
                'subscription_payment_method' => $businessDetail->subscription_payment_method,
                'subscription_payment_id' => $businessDetail->subscription_payment_id,
                'subscription_code' => $businessDetail->subscription_code,
            ]);

            Log::info('Subscription successfully cancelled for user: ' . $this->user->id);
        } else {
            Log::error('Failed to cancel subscription for user: ' . $this->user->id);
        }
    }
}
