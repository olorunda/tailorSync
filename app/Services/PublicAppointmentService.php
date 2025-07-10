<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use App\Notifications\AppointmentCreatedNotification;
use App\Notifications\AppointmentConfirmationNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class PublicAppointmentService
{
    /**
     * Get user from slug.
     *
     * @param string $slug
     * @return User|null
     */
    public function getUserFromSlug(string $slug): ?User
    {
        // Extract user ID from slug (format: business-name_userId)
        $parts = explode('_', $slug);
        $userId = end($parts);

        // Find the user by ID
        return User::find($userId);
    }

    /**
     * Check if the user's subscription plan allows public appointment booking.
     *
     * @param User $user
     * @return bool
     */
    public function canBookPublicAppointments(User $user): bool
    {
        $businessDetail = $user->businessDetail;
        return $businessDetail && SubscriptionService::canUseFeature($businessDetail, 'public_appointments_enabled');
    }

    /**
     * Get business name for the user.
     *
     * @param User $user
     * @return string
     */
    public function getBusinessName(User $user): string
    {
        $businessDetail = $user->businessDetail;
        return $businessDetail ? $businessDetail->business_name : $user->name;
    }

    /**
     * Get existing appointments for the user.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUpcomingAppointments(User $user)
    {
        return $user->appointments()
            ->where('date', '>=', now()->startOfDay())
            ->get();
    }

    /**
     * Check for appointment clashes.
     *
     * @param User $user
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @return bool
     */
    public function hasAppointmentClash(User $user, string $date, string $startTime, string $endTime): bool
    {
        $dateObj = Carbon::parse($date);
        $startTimeObj = Carbon::parse($date . ' ' . $startTime);
        $endTimeObj = Carbon::parse($date . ' ' . $endTime);

        $clashingAppointments = $user->appointments()
            ->where('date', $dateObj->toDateString())
            ->where(function ($query) use ($startTimeObj, $endTimeObj) {
                $query->where(function ($q) use ($startTimeObj, $endTimeObj) {
                    // New appointment starts during an existing appointment
                    $q->where('start_time', '<=', $startTimeObj)
                      ->where('end_time', '>', $startTimeObj);
                })->orWhere(function ($q) use ($startTimeObj, $endTimeObj) {
                    // New appointment ends during an existing appointment
                    $q->where('start_time', '<', $endTimeObj)
                      ->where('end_time', '>=', $endTimeObj);
                })->orWhere(function ($q) use ($startTimeObj, $endTimeObj) {
                    // New appointment completely contains an existing appointment
                    $q->where('start_time', '>=', $startTimeObj)
                      ->where('end_time', '<=', $endTimeObj);
                });
            })
            ->count();

        return $clashingAppointments > 0;
    }

    /**
     * Create a new client or get existing one.
     *
     * @param User $user
     * @param string $name
     * @param string $email
     * @param string|null $phone
     * @return Client
     */
    public function findOrCreateClient(User $user, string $name, string $email, ?string $phone = null): Client
    {
        return Client::firstOrCreate(
            ['email' => $email, 'user_id' => $user->id],
            [
                'name' => $name,
                'phone' => $phone,
                'user_id' => $user->id,
            ]
        );
    }

    /**
     * Create a new appointment.
     *
     * @param User $user
     * @param Client $client
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param string|null $description
     * @return Appointment
     */
    public function createAppointment(User $user, Client $client, string $date, string $startTime, string $endTime, ?string $description = null): Appointment
    {
        $dateObj = Carbon::parse($date);
        $startTimeObj = Carbon::parse($date . ' ' . $startTime);
        $endTimeObj = Carbon::parse($date . ' ' . $endTime);
        $businessDetail = $user->businessDetail;

        $appointment = new Appointment([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Appointment with ' . $client->name,
            'description' => $description,
            'start_time' => $startTimeObj,
            'end_time' => $endTimeObj,
            'date' => $dateObj->toDateString(),
            'type' => 'client_booking',
            'status' => 'scheduled',
            'location' => $businessDetail->address ?? 'To be confirmed',
        ]);

        $appointment->save();

        // Get business name for notification
        $businessName = $this->getBusinessName($user);

        // Notify the business owner
        $user->notify(new AppointmentCreatedNotification($appointment));

        // Notify the client
        if ($client->email) {
            $client->notify(new AppointmentConfirmationNotification($appointment, $businessName));
        }

        return $appointment;
    }

    /**
     * Get appointment by ID for a specific user.
     *
     * @param User $user
     * @param int $appointmentId
     * @return Appointment|null
     */
    public function getAppointment(User $user, int $appointmentId): ?Appointment
    {
        return Appointment::where('id', $appointmentId)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Get available time slots for a specific date.
     *
     * @param User $user
     * @param string $date
     * @return array
     */
    public function getAvailableTimeSlots(User $user, string $date): array
    {
        $dateObj = Carbon::parse($date);
        $dayOfWeek = strtolower($dateObj->format('l')); // Get day of week (monday, tuesday, etc.)

        // Get all appointments for the selected date
        $appointments = $user->appointments()
            ->where('date', $dateObj->toDateString())
            ->get();

        // Get business details
        $businessDetail = $user->businessDetail;

        // Check if the day is available for appointments
        $availableDays = $businessDetail->available_days ?? [];
        if (!empty($availableDays) && !in_array($dayOfWeek, $availableDays)) {
            // This day is not available for appointments
            return [];
        }

        // Define business hours (9 AM to 5 PM by default)
        $startTime = $businessDetail && $businessDetail->business_hours_start
            ? Carbon::parse($businessDetail->business_hours_start)
            : Carbon::parse('09:00:00');
        $endTime = $businessDetail && $businessDetail->business_hours_end
            ? Carbon::parse($businessDetail->business_hours_end)
            : Carbon::parse('17:00:00');

        // Generate all possible 30-minute slots
        $timeSlots = [];
        $currentSlot = Carbon::parse($dateObj->format('Y-m-d') . ' ' . $startTime->format('H:i:s'));
        $endTimeForDay = Carbon::parse($dateObj->format('Y-m-d') . ' ' . $endTime->format('H:i:s'));

        while ($currentSlot < $endTimeForDay) {
            $slotEnd = (clone $currentSlot)->addMinutes(30);

            // Check if this slot overlaps with any existing appointment
            $isAvailable = true;
            foreach ($appointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->start_time);
                $appointmentEnd = Carbon::parse($appointment->end_time);

                if (
                    ($currentSlot >= $appointmentStart && $currentSlot < $appointmentEnd) ||
                    ($slotEnd > $appointmentStart && $slotEnd <= $appointmentEnd) ||
                    ($currentSlot <= $appointmentStart && $slotEnd >= $appointmentEnd)
                ) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $timeSlots[] = [
                    'start' => $currentSlot->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'label' => $currentSlot->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                ];
            }

            $currentSlot->addMinutes(30);
        }

        return $timeSlots;
    }
}
