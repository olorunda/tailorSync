<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class PublicAppointmentController extends Controller
{
    /**
     * Display the public appointment booking page.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($slug)
    {
        // Extract user ID from slug (format: business-name_userId)
        $parts = explode('_', $slug);
        $userId = end($parts);

        // Find the user by ID
        $user = User::find($userId);

        // If no user is found, return 404
        if (!$user) {
            abort(404, 'Booking page not found');
        }

        // Get the business details
        $businessDetail = $user->businessDetail;
        $businessName = $businessDetail ? $businessDetail->business_name : $user->name;

        // Get existing appointments for availability checking
        $appointments = $user->appointments()
            ->where('date', '>=', now()->startOfDay())
            ->get();

        return view('public.appointment-booking', [
            'user' => $user,
            'businessName' => $businessName,
            'appointments' => $appointments,
        ]);
    }

    /**
     * Store a new appointment from the public booking page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $slug)
    {
        // Extract user ID from slug (format: business-name_userId)
        $parts = explode('_', $slug);
        $userId = end($parts);

        // Find the user by ID
        $user = User::find($userId);

        // If no user is found, return 404
        if (!$user) {
            abort(404, 'Booking page not found');
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Format date and times
        $date = Carbon::parse($request->date);
        $startTime = Carbon::parse($request->date . ' ' . $request->start_time);
        $endTime = Carbon::parse($request->date . ' ' . $request->end_time);

        // Check for appointment clashes
        $clashingAppointments = $user->appointments()
            ->where('date', $date->toDateString())
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // New appointment starts during an existing appointment
                    $q->where('start_time', '<=', $startTime)
                      ->where('end_time', '>', $startTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // New appointment ends during an existing appointment
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>=', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // New appointment completely contains an existing appointment
                    $q->where('start_time', '>=', $startTime)
                      ->where('end_time', '<=', $endTime);
                });
            })
            ->count();

        if ($clashingAppointments > 0) {
            return back()
                ->withErrors(['time_clash' => 'The selected time slot is not available. Please choose another time.'])
                ->withInput();
        }

        // Find or create client
        $client = Client::firstOrCreate(
            ['email' => $request->email, 'user_id' => $user->id],
            [
                'name' => $request->name,
                'phone' => $request->phone,
                'user_id' => $user->id,
            ]
        );

        // Create the appointment
        $appointment = new Appointment([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Appointment with ' . $client->name,
            'description' => $request->description,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'date' => $date->toDateString(),
            'type' => 'client_booking',
            'status' => 'scheduled',
            'location' => $businessDetail->address ?? 'To be confirmed',
        ]);

        $appointment->save();

        // Redirect to confirmation page
        return redirect()->route('appointments.public.confirmation', [
            'slug' => $slug,
            'appointment' => $appointment->id
        ])->with('success', 'Your appointment has been booked successfully!');
    }

    /**
     * Display the appointment confirmation page.
     *
     * @param  string  $slug
     * @param  int  $appointmentId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function confirmation($slug, $appointmentId)
    {
        // Extract user ID from slug (format: business-name_userId)
        $parts = explode('_', $slug);
        $userId = end($parts);

        // Find the user by ID
        $user = User::find($userId);

        // If no user is found, return 404
        if (!$user) {
            abort(404, 'Booking page not found');
        }

        // Find the appointment
        $appointment = Appointment::where('id', $appointmentId)
            ->where('user_id', $user->id)
            ->first();

        // If no appointment is found, return 404
        if (!$appointment) {
            abort(404, 'Appointment not found');
        }

        // Get the business details
        $businessDetail = $user->businessDetail;
        $businessName = $businessDetail ? $businessDetail->business_name : $user->name;

        return view('public.appointment-confirmation', [
            'user' => $user,
            'businessName' => $businessName,
            'appointment' => $appointment,
        ]);
    }

    /**
     * Get available time slots for a specific date.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableTimeSlots(Request $request, $slug)
    {
        // Extract user ID from slug (format: business-name_userId)
        $parts = explode('_', $slug);
        $userId = end($parts);

        // Find the user by ID
        $user = User::find($userId);

        // If no user is found, return error
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $date = Carbon::parse($request->date);

        // Get all appointments for the selected date
        $appointments = $user->appointments()
            ->where('date', $date->toDateString())
            ->get();

        // Define business hours (9 AM to 5 PM by default)
        $businessDetail = $user->businessDetail;
        $startHour = $businessDetail && $businessDetail->business_hours_start
            ? Carbon::parse($businessDetail->business_hours_start)->hour
            : 9;
        $endHour = $businessDetail && $businessDetail->business_hours_end
            ? Carbon::parse($businessDetail->business_hours_end)->hour
            : 17;

        // Generate all possible 30-minute slots
        $timeSlots = [];
        $currentSlot = Carbon::parse($date->format('Y-m-d') . ' ' . $startHour . ':00:00');
        $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $endHour . ':00:00');

        while ($currentSlot < $endTime) {
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

        return response()->json(['timeSlots' => $timeSlots]);
    }
}
