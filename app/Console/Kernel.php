<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule the appointment reminder command to run daily at 8 AM
        $schedule->command('app:send-appointment-reminders')->dailyAt('08:00');

        // Schedule the subscription upgrade reminder command to run weekly on Mondays at 9 AM
        $schedule->command('app:send-subscription-upgrade-reminders')->weekly()->mondays()->at('09:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
