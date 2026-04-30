<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\PushSubscription;
use App\Services\PushNotificationService;

class TestPushNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:test {user_id? : The ID of the user to notify} {--title=Test Notification} {--body=This is a test push notification from TailorSync.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test push notification to a user';

    /**
     * Execute the console command.
     */
    public function handle(PushNotificationService $pushService)
    {
        $userId = $this->argument('user_id');
        $title = $this->option('title');
        $body = $this->option('body');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
            $subscriptions = $user->pushSubscriptions;
        } else {
            $this->info("No user ID provided. Attempting to send to the first available subscription in the database...");
            $subscription = PushSubscription::first();
            if (!$subscription) {
                $this->error("No push subscriptions found in the database.");
                return 1;
            }
            $subscriptions = collect([$subscription]);
        }

        if ($subscriptions->isEmpty()) {
            $this->error("No push subscriptions found for this user.");
            return 1;
        }

        $this->info("Sending test notification to " . $subscriptions->count() . " subscription(s)...");

        $data = [
            'title' => $title,
            'body' => $body,
            'icon' => '/apple-touch-icon.png',
            'badge' => '/apple-touch-icon.png',
            'url' => url('/'),
            'data' => [
                'test' => true,
                'sent_at' => now()->toIso8601String(),
            ]
        ];

        $successCount = 0;
        foreach ($subscriptions as $subscription) {
            $this->comment("Sending to endpoint: " . substr($subscription->endpoint, 0, 50) . "...");
            if ($pushService->sendToSubscription($subscription, $data)) {
                $this->info("✅ Successfully sent!");
                $successCount++;
            } else {
                $this->error("❌ Failed to send. Check logs for details.");
            }
        }

        $this->info("Summary: {$successCount} successful, " . ($subscriptions->count() - $successCount) . " failed.");

        return 0;
    }
}
