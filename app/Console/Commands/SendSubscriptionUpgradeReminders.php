<?php

namespace App\Console\Commands;

use App\Models\BusinessDetail;
use App\Models\User;
use App\Notifications\SubscriptionUpgradeReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendSubscriptionUpgradeReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-subscription-upgrade-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to business admins who have not upgraded their subscription plan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to send subscription upgrade reminders...');

        // Find business admins who haven't upgraded (on free plan or basic plan)
        $businessDetails = BusinessDetail::whereIn('subscription_plan', ['free', 'basic'])
            ->where('subscription_active', true)
            ->get();

        $count = 0;

        foreach ($businessDetails as $businessDetail) {
            // Get the user (business admin) associated with this business detail
            $user = $businessDetail->user;

            // Skip if user doesn't exist
            if (!$user) {
                continue;
            }

            // Skip if user is not a parent account (only send to main business admins, not team members)
            if ($user->parent_id) {
                continue;
            }

            try {
                // Send the upgrade reminder notification
                $user->notify(new SubscriptionUpgradeReminderNotification($businessDetail));
                $count++;

                $this->info("Sent upgrade reminder to {$user->name} ({$user->email}) - Current plan: {$businessDetail->subscription_plan}");
            } catch (\Exception $e) {
                $this->error("Failed to send upgrade reminder to {$user->email}: {$e->getMessage()}");
                Log::error("Failed to send upgrade reminder", [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Sent {$count} subscription upgrade reminders.");

        return 0;
    }
}
