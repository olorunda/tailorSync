<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the all-in-one reminder command to run daily at 8 AM
Schedule::command('reminders:send')->dailyAt('08:00');

// Schedule the appointment reminder command to run daily at 8 AM
Schedule::command('app:send-appointment-reminders')->dailyAt('08:00');

// Schedule the subscription upgrade reminder command to run weekly on Mondays at 9 AM
Schedule::command('app:send-subscription-upgrade-reminders')->weekly()->mondays()->at('09:00');

// Monitor and run the queue worker if it's not running
// Added withoutOverlapping() to prevent process explosion
Schedule::command('queue:work --tries=1 --sleep=1 --queue=default')
    ->everyMinute()
    ->withoutOverlapping()
    ->before(function () {
        Artisan::call("queue:restart");
    });
