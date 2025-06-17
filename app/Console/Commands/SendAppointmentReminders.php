<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Notifications\AppointmentReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-appointment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for upcoming appointments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get appointments that are 24 hours away and haven't had reminders sent
        $appointments = Appointment::where('start_time', '>', Carbon::now())
            ->where('start_time', '<', Carbon::now()->addHours(24))
            ->where('reminder_sent', false)
            ->where('status', '!=', 'cancelled')
            ->get();

        $count = 0;

        foreach ($appointments as $appointment) {
            // Send notification to the client
            if ($appointment->client) {
                $appointment->client->notify(new AppointmentReminderNotification($appointment));
                $count++;
            }

            // Send notification to the user (tailor/business owner)
            if ($appointment->user) {
                $appointment->user->notify(new AppointmentReminderNotification($appointment));
                $count++;
            }

            // Mark reminder as sent
            $appointment->reminder_sent = true;
            $appointment->save();
        }

        $this->info("Sent {$count} appointment reminders for " . $appointments->count() . " appointments.");

        return 0;
    }
}
