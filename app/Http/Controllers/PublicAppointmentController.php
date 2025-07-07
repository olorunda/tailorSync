<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetAvailableTimeSlotsRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Services\PublicAppointmentService;
use Illuminate\Http\Request;

class PublicAppointmentController extends Controller
{
    /**
     * The public appointment service instance.
     *
     * @var \App\Services\PublicAppointmentService
     */
    protected $publicAppointmentService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\PublicAppointmentService $publicAppointmentService
     * @return void
     */
    public function __construct(PublicAppointmentService $publicAppointmentService)
    {
        $this->publicAppointmentService = $publicAppointmentService;
    }

    /**
     * Display the public appointment booking page.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($slug)
    {
        $user = $this->publicAppointmentService->getUserFromSlug($slug);

        // If no user is found, return 404
        if (!$user) {
            abort(404, 'Booking page not found');
        }

        // Check if the user's subscription plan allows public appointment booking
        if (!$this->publicAppointmentService->canBookPublicAppointments($user)) {
            abort(403, 'Public appointment booking is not available for this business');
        }

        // Get the business name
        $businessName = $this->publicAppointmentService->getBusinessName($user);

        // Get existing appointments for availability checking
        $appointments = $this->publicAppointmentService->getUpcomingAppointments($user);

        return view('public.appointment-booking', [
            'user' => $user,
            'businessName' => $businessName,
            'appointments' => $appointments,
        ]);
    }

    /**
     * Store a new appointment from the public booking page.
     *
     * @param  \App\Http\Requests\StoreAppointmentRequest  $request
     * @param  string  $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreAppointmentRequest $request, $slug)
    {
        $user = $this->publicAppointmentService->getUserFromSlug($slug);

        // If no user is found, return 404
        if (!$user) {
            abort(404, 'Booking page not found');
        }

        // Check if the user's subscription plan allows public appointment booking
        if (!$this->publicAppointmentService->canBookPublicAppointments($user)) {
            abort(403, 'Public appointment booking is not available for this business');
        }

        // Check for appointment clashes
        if ($this->publicAppointmentService->hasAppointmentClash(
            $user,
            $request->date,
            $request->start_time,
            $request->end_time
        )) {
            return back()
                ->withErrors(['time_clash' => 'The selected time slot is not available. Please choose another time.'])
                ->withInput();
        }

        // Find or create client
        $client = $this->publicAppointmentService->findOrCreateClient(
            $user,
            $request->name,
            $request->email,
            $request->phone
        );

        // Create the appointment
        $appointment = $this->publicAppointmentService->createAppointment(
            $user,
            $client,
            $request->date,
            $request->start_time,
            $request->end_time,
            $request->description
        );

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
        $user = $this->publicAppointmentService->getUserFromSlug($slug);

        // If no user is found, return 404
        if (!$user) {
            abort(404, 'Booking page not found');
        }

        // Check if the user's subscription plan allows public appointment booking
        if (!$this->publicAppointmentService->canBookPublicAppointments($user)) {
            abort(403, 'Public appointment booking is not available for this business');
        }

        // Find the appointment
        $appointment = $this->publicAppointmentService->getAppointment($user, $appointmentId);

        // If no appointment is found, return 404
        if (!$appointment) {
            abort(404, 'Appointment not found');
        }

        // Get the business name
        $businessName = $this->publicAppointmentService->getBusinessName($user);

        return view('public.appointment-confirmation', [
            'user' => $user,
            'businessName' => $businessName,
            'appointment' => $appointment,
        ]);
    }

    /**
     * Get available time slots for a specific date.
     *
     * @param  \App\Http\Requests\GetAvailableTimeSlotsRequest  $request
     * @param  string  $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableTimeSlots(GetAvailableTimeSlotsRequest $request, $slug)
    {
        $user = $this->publicAppointmentService->getUserFromSlug($slug);

        // If no user is found, return error
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Check if the user's subscription plan allows public appointment booking
        if (!$this->publicAppointmentService->canBookPublicAppointments($user)) {
            return response()->json(['error' => 'Public appointment booking is not available for this business'], 403);
        }

        // Get available time slots
        $timeSlots = $this->publicAppointmentService->getAvailableTimeSlots($user, $request->date);

        return response()->json(['timeSlots' => $timeSlots]);
    }
}
