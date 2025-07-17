<?php

namespace App\Notifications;

use App\Models\BusinessDetail;
use App\Services\SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionUpgradeReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $businessDetail;
    protected $currentPlanKey;

    /**
     * Create a new notification instance.
     */
    public function __construct(BusinessDetail $businessDetail)
    {
        $this->businessDetail = $businessDetail;
        $this->currentPlanKey = $businessDetail->subscription_plan ?? 'free';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $currentPlan = SubscriptionService::getPlan($this->currentPlanKey);
        $currentPlanName = $currentPlan ? $currentPlan['name'] : ucfirst($this->currentPlanKey);

        // Get the next tier plan (basic if free, premium if basic)
        $nextPlanKey = $this->currentPlanKey === 'free' ? 'basic' : 'premium';
        $nextPlan = SubscriptionService::getPlan($nextPlanKey);
        $nextPlanName = $nextPlan ? $nextPlan['name'] : ucfirst($nextPlanKey);

        $currencySymbol = SubscriptionService::getCurrencySymbol();
        $nextPlanPrice = $nextPlan ? $nextPlan['price'] : 0;

        // Get key features of the next plan to highlight
        $keyFeatures = $this->getKeyFeatures($nextPlanKey);

        $message = (new MailMessage)
            ->subject("Upgrade Your Plan to Unlock More Features")
            ->greeting("Hello {$notifiable->name},")
            ->line("You're currently using the {$currentPlanName} Plan. Upgrade to the {$nextPlanName} Plan to unlock more powerful features for your business!")
            ->line("Why upgrade to {$nextPlanName}?");

        // Add key features as bullet points
        foreach ($keyFeatures as $feature) {
            $message->line("• {$feature}");
        }

        return $message
            ->line("Price: {$currencySymbol}" . number_format($nextPlanPrice, 2) . " per month")
            ->action('Upgrade Now', route('subscriptions.index'))
            ->line("Upgrading will help you grow your business and provide better service to your clients.")
            ->line("Thank you for choosing our service!");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $currentPlan = SubscriptionService::getPlan($this->currentPlanKey);
        $currentPlanName = $currentPlan ? $currentPlan['name'] : ucfirst($this->currentPlanKey);

        $nextPlanKey = $this->currentPlanKey === 'free' ? 'basic' : 'premium';
        $nextPlan = SubscriptionService::getPlan($nextPlanKey);
        $nextPlanName = $nextPlan ? $nextPlan['name'] : ucfirst($nextPlanKey);

        $currencySymbol = SubscriptionService::getCurrencySymbol();
        $nextPlanPrice = $nextPlan ? $nextPlan['price'] : 0;

        $keyFeatures = $this->getKeyFeatures($nextPlanKey);
        $featureLines = array_map(function($feature) {
            return "• {$feature}";
        }, $keyFeatures);

        // Store the mail message components
        $mailData = [
            'subject' => "Upgrade Your Plan to Unlock More Features",
            'greeting' => "Hello {$notifiable->name},",
            'lines' => array_merge([
                "You're currently using the {$currentPlanName} Plan. Upgrade to the {$nextPlanName} Plan to unlock more powerful features for your business!",
                "Why upgrade to {$nextPlanName}?"
            ], $featureLines, [
                "Price: {$currencySymbol}" . number_format($nextPlanPrice, 2) . " per month",
                "Upgrading will help you grow your business and provide better service to your clients.",
                "Thank you for choosing our service!"
            ]),
            'action' => [
                'text' => 'Upgrade Now',
                'url' => route('subscriptions.index')
            ]
        ];

        return [
            'business_detail_id' => $this->businessDetail->id,
            'current_subscription_plan' => $this->currentPlanKey,
            'recommended_plan' => $nextPlanKey,
            'mail' => $mailData
        ];
    }

    /**
     * Get key features to highlight for the given plan
     *
     * @param string $planKey
     * @return array
     */
    private function getKeyFeatures(string $planKey): array
    {
        if ($planKey === 'basic') {
            return [
                'Store enabled - Sell your products online',
                'Up to 50 products in your inventory',
                'Up to 20 designs saved',
                'Appointment scheduling',
                'Payment integration',
                'Up to 5 team members'
            ];
        } elseif ($planKey === 'premium') {
            return [
                'Unlimited products in your inventory',
                'Unlimited designs',
                'Unlimited team members',
                'Custom domain for your store',
                'Tax reports',
                'AI style suggestions',
                'All payment gateways supported'
            ];
        }

        return [];
    }
}
