<?php

namespace App\Notifications;

use App\Models\BusinessDetail;
use App\Services\SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $businessDetail;
    protected $planKey;

    /**
     * Create a new notification instance.
     */
    public function __construct(BusinessDetail $businessDetail)
    {
        $this->businessDetail = $businessDetail;
        $this->planKey = $businessDetail->subscription_plan;
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
        $plan = SubscriptionService::getPlan($this->planKey);
        $planName = $plan ? $plan['name'] : ucfirst($this->planKey);
        $endDate = $this->businessDetail->subscription_end_date ? $this->businessDetail->subscription_end_date->format('F j, Y') : 'N/A';
        $currencySymbol = SubscriptionService::getCurrencySymbol();
        $price = $plan ? $plan['price'] : 0;

        return (new MailMessage)
            ->subject("Subscription Confirmation - {$planName} Plan")
            ->greeting("Hello {$notifiable->name},")
            ->line("Thank you for subscribing to the {$planName} Plan!")
            ->line("Your subscription has been successfully activated.")
            ->line("Subscription Details:")
            ->line("Plan: {$planName}")
            ->line("Price: {$currencySymbol}" . number_format($price, 2))
            ->line("Valid Until: {$endDate}")
            ->action('Manage Subscription', route('subscriptions.index'))
            ->line('Thank you for choosing our service!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $plan = SubscriptionService::getPlan($this->planKey);
        $planName = $plan ? $plan['name'] : ucfirst($this->planKey);
        $endDate = $this->businessDetail->subscription_end_date ? $this->businessDetail->subscription_end_date->format('F j, Y') : 'N/A';
        $currencySymbol = SubscriptionService::getCurrencySymbol();
        $price = $plan ? $plan['price'] : 0;

        // Store the mail message components
        $mailData = [
            'subject' => "Subscription Confirmation - {$planName} Plan",
            'greeting' => "Hello {$notifiable->name},",
            'lines' => [
                "Thank you for subscribing to the {$planName} Plan!",
                "Your subscription has been successfully activated.",
                "Subscription Details:",
                "Plan: {$planName}",
                "Price: {$currencySymbol}" . number_format($price, 2),
                "Valid Until: {$endDate}",
                'Thank you for choosing our service!'
            ],
            'action' => [
                'text' => 'Manage Subscription',
                'url' => route('subscriptions.index')
            ]
        ];

        return [
            'business_detail_id' => $this->businessDetail->id,
            'subscription_plan' => $this->planKey,
            'subscription_end_date' => $this->businessDetail->subscription_end_date,
            'mail' => $mailData
        ];
    }
}
