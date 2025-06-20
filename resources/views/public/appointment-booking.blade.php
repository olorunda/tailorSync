<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Book an Appointment - {{ $businessName }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- FullCalendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
</head>
<body class="font-sans antialiased bg-zinc-100 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
    <div class="min-h-screen">
        <header class="bg-white dark:bg-zinc-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div class="flex items-center">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center">
                        <a href="/">
                            <x-app-logo-icon class="block h-9 w-auto" />
                        </a>
                    </div>
                    <h1 class="ml-4 text-xl font-semibold">Book an Appointment with {{ $businessName }}</h1>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Calendar Card -->
                <div class="lg:col-span-2 bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Select a Date</h2>
                        <div id="calendar" class="h-96"></div>
                    </div>
                </div>

                <!-- Booking Form Card -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Book Your Appointment</h2>

                        @if ($errors->any())
                            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-400 rounded-lg">
                                <ul class="list-disc pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('appointments.public.store', ['slug' => $user->getBusinessSlug()]) }}" id="booking-form">
                            @csrf
                            <div class="space-y-4">
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                        class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                        class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Phone</label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                        class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                </div>

                                <!-- Date -->
                                <div>
                                    <label for="date" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Date</label>
                                    <input type="date" name="date" id="date" value="{{ old('date') }}" required
                                        class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                </div>

                                <!-- Time Slot -->
                                <div>
                                    <label for="time_slot" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Time Slot</label>
                                    <select name="time_slot" id="time_slot" required
                                        class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                        <option value="">Select a date first</option>
                                    </select>
                                    <input type="hidden" name="start_time" id="start_time">
                                    <input type="hidden" name="end_time" id="end_time">
                                </div>

                                <!-- Description -->
                                <div>
                                    <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
                                    <textarea name="description" id="description" rows="3"
                                        class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-orange-500 focus:ring-orange-500">{{ old('description') }}</textarea>
                                </div>

                                <div>
                                    <button type="submit"
                                        class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                        Book Appointment
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-white dark:bg-zinc-800 shadow mt-6">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                    &copy; {{ date('Y') }} {{ $businessName }}. All rights reserved.
                </p>
            </div>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize FullCalendar
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                selectable: true,
                select: function(info) {
                    // Update the date input
                    document.getElementById('date').value = info.startStr;

                    // Fetch available time slots for the selected date
                    fetchTimeSlots(info.startStr);
                },
                events: [
                    // Add existing appointments as events
                    @foreach($appointments as $appointment)
                    {
                        title: 'Booked',
                        start: '{{ $appointment->start_time->format('Y-m-d\TH:i:s') }}',
                        end: '{{ $appointment->end_time->format('Y-m-d\TH:i:s') }}',
                        color: '#f97316', // Orange color
                        display: 'block'
                    },
                    @endforeach
                ]
            });
            calendar.render();

            // Handle date input change
            document.getElementById('date').addEventListener('change', function() {
                fetchTimeSlots(this.value);

                // Update calendar selection
                calendar.gotoDate(this.value);
            });

            // Handle time slot selection
            document.getElementById('time_slot').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const [start, end] = selectedOption.value.split('-');
                    document.getElementById('start_time').value = start;
                    document.getElementById('end_time').value = end;
                }
            });

            // Function to fetch available time slots
            function fetchTimeSlots(date) {
                const timeSlotSelect = document.getElementById('time_slot');

                // Clear existing options
                timeSlotSelect.innerHTML = '<option value="">Loading time slots...</option>';

                // Fetch available time slots from the server
                fetch(`{{ route('appointments.public.time-slots', ['slug' => $user->getBusinessSlug()]) }}?date=${date}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    timeSlotSelect.innerHTML = '';

                    if (data.timeSlots && data.timeSlots.length > 0) {
                        timeSlotSelect.innerHTML = '<option value="">Select a time slot</option>';

                        data.timeSlots.forEach(slot => {
                            const option = document.createElement('option');
                            option.value = `${slot.start}-${slot.end}`;
                            option.textContent = slot.label;
                            timeSlotSelect.appendChild(option);
                        });
                    } else {
                        timeSlotSelect.innerHTML = '<option value="">No available time slots</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching time slots:', error);
                    timeSlotSelect.innerHTML = '<option value="">Error loading time slots</option>';
                });
            }
        });
    </script>
</body>
</html>
