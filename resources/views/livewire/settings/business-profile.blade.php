@section('breadcrumbs')
    <flux:breadcrumbs.item href="{{ route('settings.profile') }}">{{ __('Settings') }}</flux:breadcrumbs.item>
    <flux:breadcrumbs.item href="{{ route('settings.business-profile') }}" current>{{ __('Business Profile') }}</flux:breadcrumbs.item>
@endsection

<x-settings.layout>
    <x-slot:heading>
        {{ __('Business Profile') }}
    </x-slot>
    <x-slot:subheading>
        {{ __('View your business information and public booking QR code') }}
    </x-slot>

    <!-- Print Button -->


    <div id="businessProfileCard">
        <!-- Business Profile Header with Gradient -->
{{--        <div class="bg-gradient-to-r from-orange-500 to-pink-500 p-6 text-white">--}}
{{--            <h2 class="text-2xl font-bold mb-2">{{ $businessName ?? 'Your Business' }}</h2>--}}
{{--            <p class="opacity-90">Professional Tailoring Services</p>--}}
{{--        </div>--}}

        <div class="p-8">
            <div class="mb-4 flex justify-end print:hidden">
                <!-- Print button placeholder -->
                @if($businessProfileUrl)
                <!-- Floating Share Button -->
                <div class="fixed top-6 right-6 z-50 print:hidden" x-data="{ open: false, pulse: true }">
                    <!-- Main Share Button -->
                    <button
                        @click="open = !open; pulse = false"
                        class="flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-r from-orange-500 to-pink-500 text-white shadow-lg hover:shadow-xl transform transition-all duration-300 hover:scale-110 focus:outline-none"
                        x-bind:class="{ 'animate-bounce': pulse }"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                    </button>

                    <!-- Share Options -->
                    <div
                        x-show="open"
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-90"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-90"
                        class="absolute top-16 right-0 mt-2 flex flex-col space-y-2 items-center"
                    >
                        <!-- View Public Profile -->
                        <a
                            href="{{ $businessProfileUrl }}"
                            target="_blank"
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-green-500 text-white shadow-md hover:shadow-lg transform transition-all duration-300 hover:scale-110"
                            data-tooltip="View Public Profile"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>

                        <!-- Facebook -->
                        <a
                            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($businessProfileUrl) }}"
                            target="_blank"
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white shadow-md hover:shadow-lg transform transition-all duration-300 hover:scale-110"
                            data-tooltip="Share on Facebook"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/>
                            </svg>
                        </a>

                        <!-- Instagram -->
                        <button
                            onclick="copyToClipboard('{{ $businessProfileUrl }}', 'instagram')"
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-purple-600 via-pink-500 to-orange-400 text-white shadow-md hover:shadow-lg transform transition-all duration-300 hover:scale-110"
                            data-tooltip="Copy for Instagram"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </button>

                        <!-- TikTok -->
                        <button
                            onclick="copyToClipboard('{{ $businessProfileUrl }}', 'tiktok')"
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-black text-white shadow-md hover:shadow-lg transform transition-all duration-300 hover:scale-110"
                            data-tooltip="Copy for TikTok"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                            </svg>
                        </button>

                        <!-- WhatsApp -->
                        <a
                            href="https://api.whatsapp.com/send?text={{ urlencode('Check out this business: ' . $businessName . ' - ' . $businessProfileUrl) }}"
                            target="_blank"
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-green-600 text-white shadow-md hover:shadow-lg transform transition-all duration-300 hover:scale-110"
                            data-tooltip="Share on WhatsApp"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                        </a>
                    </div>

                    <!-- Tooltip Style -->
                    <style>
                        [data-tooltip]:hover:after {
                            content: attr(data-tooltip);
                            position: absolute;
                            right: 100%;
                            margin-right: 10px;
                            top: 50%;
                            transform: translateY(-50%);
                            background-color: rgba(0, 0, 0, 0.8);
                            color: white;
                            padding: 4px 8px;
                            border-radius: 4px;
                            font-size: 12px;
                            white-space: nowrap;
                            z-index: 10;
                        }
                    </style>
                </div>

                <!-- Copy Link Feedback -->
                <div id="copyFeedback" class="fixed top-24 right-6 z-50 bg-white dark:bg-zinc-800 p-3 rounded-lg shadow-lg hidden">
                    <p class="text-green-600 dark:text-green-400 text-sm font-medium"></p>
                </div>

                <!-- Copy to Clipboard Script -->
                <script>
                    function copyToClipboard(text, platform) {
                        navigator.clipboard.writeText(text).then(function() {
                            // Show feedback
                            const feedback = document.getElementById('copyFeedback');
                            feedback.classList.remove('hidden');
                            feedback.querySelector('p').textContent = `Link copied! Share it on ${platform.charAt(0).toUpperCase() + platform.slice(1)}`;

                            // Hide feedback after 3 seconds
                            setTimeout(function() {
                                feedback.classList.add('hidden');
                            }, 3000);
                        }, function(err) {
                            console.error('Could not copy text: ', err);
                        });
                    }

                    // Start pulsing animation after 2 seconds
                    document.addEventListener('DOMContentLoaded', function() {
                        setTimeout(function() {
                            if (typeof Alpine !== 'undefined') {
                                // Alpine.js is available
                                const shareButton = document.querySelector('[x-data]');
                                if (shareButton && shareButton.__x) {
                                    shareButton.__x.updateElements(shareButton);
                                }
                            }
                        }, 2000);
                    });
                </script>
                @endif
{{--                <button id="printButton" class="flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg font-medium transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">--}}
{{--                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />--}}
{{--                    </svg>--}}
{{--                    Print Business Profile--}}
{{--                </button>--}}
            </div>
            <!-- Combined Business Information and QR Code Card -->
            <div class="print_this_only bg-gradient-to-b from-orange-50 to-zinc-50 dark:from-zinc-700/50 dark:to-zinc-800/50 p-6 rounded-xl shadow-md border border-orange-100 dark:border-zinc-600 transform transition-all duration-300 hover:shadow-lg">
                <!-- Business Logo and Header -->
                <div class="flex flex-col md:flex-row items-center mb-8">
                    <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-8">
                        @if($logo)
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-r from-orange-200 to-pink-200 rounded-full blur-md transform -translate-y-1"></div>
                                <img src="{{ asset('storage/' . $logo) }}" alt="{{ $businessName }} Logo" class="relative h-32 w-auto rounded-full border-4 border-white dark:border-zinc-700 shadow-md">
                            </div>
                        @else
                            <div class="h-32 w-32 bg-gradient-to-r from-orange-400 to-pink-400 rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-md">
                                {{ $businessName ? substr($businessName, 0, 1) : 'B' }}
                            </div>
                        @endif
                    </div>

                    <div class="text-center md:text-left">
                        <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">{{ $businessName ?? 'Your Business' }}</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-3">Professional Tailoring Services</p>
                        <div class="h-1 w-24 bg-gradient-to-r from-orange-400 to-pink-400 rounded-full mx-auto md:mx-0"></div>
                    </div>
                </div>

                <!-- Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
                    <!-- Business Information -->
                    <div>
                        <div class="bg-white dark:bg-zinc-800/60 p-6 rounded-xl shadow-sm border border-orange-100 dark:border-zinc-700 h-full">
                            <h3 class="text-lg font-semibold text-orange-700 dark:text-orange-300 mb-4 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Business Details
                            </h3>

                            <div class="space-y-4">
                                <div class="flex flex-col sm:flex-row border-b border-orange-100 dark:border-zinc-600 pb-3">
                                    <div class="sm:w-1/3 text-orange-600 dark:text-orange-400 font-medium mb-1 sm:mb-0">Name</div>
                                    <div class="sm:w-2/3 text-zinc-900 dark:text-white font-semibold break-words">{{ $businessName ?? 'Not set' }}</div>
                                </div>

                                <div class="flex flex-col sm:flex-row border-b border-orange-100 dark:border-zinc-600 pb-3">
                                    <div class="sm:w-1/3 text-orange-600 dark:text-orange-400 font-medium mb-1 sm:mb-0">Address</div>
                                    <div class="sm:w-2/3 text-zinc-900 dark:text-white whitespace-pre-line break-words">{{ $businessAddress ?? 'Not set' }}</div>
                                </div>

                                <div class="flex flex-col sm:flex-row border-b border-orange-100 dark:border-zinc-600 pb-3">
                                    <div class="sm:w-1/3 text-orange-600 dark:text-orange-400 font-medium mb-1 sm:mb-0">Phone</div>
                                    <div class="sm:w-2/3 text-zinc-900 dark:text-white break-words">{{ $businessPhone ?? 'Not set' }}</div>
                                </div>

                                <div class="flex flex-col sm:flex-row">
                                    <div class="sm:w-1/3 text-orange-600 dark:text-orange-400 font-medium mb-1 sm:mb-0">Email</div>
                                    <div class="sm:w-2/3 text-zinc-900 dark:text-white break-words">{{ $businessEmail ?? 'Not set' }}</div>
                                </div>
                                <div class="flex flex-col sm:flex-row">
                                    <div class="sm:w-1/3 text-orange-600 dark:text-orange-400 font-medium mb-1 sm:mb-0">Booking Link</div>
                                    <div class="sm:w-2/3 text-zinc-900 dark:text-white break-words">
                                        <a href="{{ $bookingUrl }}" target="_blank" class="text-orange-600 hover:text-orange-700 dark:text-orange-500 dark:hover:text-orange-400 font-medium underline decoration-dotted underline-offset-4 break-all">
                                            {{ $bookingUrl }}
                                        </a>
                                    </div>
                                </div>

{{--                            <div class="pt-6 print:hidden">--}}
{{--                                <a href="{{ route('settings.business') }}" class="px-5 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg font-medium transition-all duration-300 inline-block shadow-md hover:shadow-lg transform hover:-translate-y-1">--}}
{{--                                    <span class="flex items-center">--}}
{{--                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">--}}
{{--                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />--}}
{{--                                        </svg>--}}
{{--                                        Edit Business Details--}}
{{--                                    </span>--}}
{{--                                </a>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <!-- QR Code for Booking Link -->--}}
{{--                    <div class="bg-white dark:bg-zinc-800/60 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 flex flex-col items-center justify-center h-full">--}}
                    <div>
                        @if($bookingUrl)
{{--                            <div class="mb-6">--}}
{{--                                <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200 mb-2 text-center">Public Booking QR Code</h3>--}}
{{--                                <div class="h-1 w-24 bg-gradient-to-r from-orange-400 to-pink-400 rounded-full mx-auto"></div>--}}
{{--                            </div>--}}

{{--                            <div class="bg-white dark:bg-zinc-800 p-4 rounded-xl shadow-md border border-zinc-200 dark:border-zinc-600 mb-6">--}}
                                <div class="flex flex-col sm:flex-row">
                                    <div class="flex justify-center w-full">
                                        <div id="qrcode"
                                             class="flex justify-center p-6 bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-orange-100 dark:border-zinc-700 transition-all duration-300"></div>
                                    </div>

                                </div>

                            {{--                            </div>--}}

{{--                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-100 dark:border-blue-800 mb-6 w-full">--}}
{{--                                <p class="text-sm text-blue-700 dark:text-blue-300 text-center flex items-center">--}}
{{--                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">--}}
{{--                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />--}}
{{--                                    </svg>--}}
{{--                                    Scan this QR code to access your public booking page--}}
{{--                                </p>--}}
{{--                            </div>--}}

{{--                            <div class="text-center mb-4 w-full">--}}
{{--                                <a href="{{ $bookingUrl }}" target="_blank" class="text-orange-600 hover:text-orange-700 dark:text-orange-500 dark:hover:text-orange-400 font-medium underline decoration-dotted underline-offset-4 break-all">--}}
{{--                                    {{ $bookingUrl }}--}}
{{--                                </a>--}}
{{--                            </div>--}}

{{--                            <div class="mt-4 print:hidden">--}}
{{--                                <a href="{{ route('settings.public-booking') }}" class="px-4 py-2 bg-gradient-to-r from-zinc-500 to-zinc-600 hover:from-zinc-600 hover:to-zinc-700 text-white rounded-lg font-medium transition-all duration-300 inline-block shadow-md hover:shadow-lg transform hover:-translate-y-1">--}}
{{--                                    <span class="flex items-center">--}}
{{--                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">--}}
{{--                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />--}}
{{--                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />--}}
{{--                                        </svg>--}}
{{--                                        Manage Booking URL--}}
{{--                                    </span>--}}
{{--                                </a>--}}
{{--                            </div>--}}

                            <!-- QR Code Generation Script -->
                            <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js" crossorigin="anonymous"></script>
                            <!-- Fallback if the first CDN fails -->
                            <script>
                                window.addEventListener('error', function(e) {
                                    if (e.target.src && e.target.src.includes('qrcodejs')) {
                                        var fallbackScript = document.createElement('script');
                                        fallbackScript.src = "https://unpkg.com/qrcodejs@1.0.0/qrcode.min.js";
                                        document.head.appendChild(fallbackScript);
                                        console.log('Loaded QR code library from fallback source');
                                    }
                                }, true);
                            </script>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Clear previous content
                                    var qrcodeElement = document.getElementById('qrcode');
                                    qrcodeElement.innerHTML = '';

                                    try {
                                        // Generate QR code
                                        new QRCode(qrcodeElement, {
                                            text: '{{ $bookingUrl }}',
                                            width: 200,
                                            height: 200,
                                            colorDark: '#000000',
                                            colorLight: '#ffffff',
                                            correctLevel: QRCode.CorrectLevel.H
                                        });
                                    } catch (error) {
                                        console.error('Error generating QR code:', error);
                                        // Fallback: Display a message and the URL as text
                                        qrcodeElement.innerHTML = '<div class="text-center p-4 w-full">' +
                                            '<p class="text-red-500 mb-2">Could not generate QR code</p>' +
                                            '<a href="{{ $bookingUrl }}" target="_blank" class="text-orange-600 hover:text-orange-700 break-all inline-block w-full">' +
                                            '{{ $bookingUrl }}' +
                                            '</a></div>';
                                    }
                                });
                            </script>
                        @else
                            <div class="text-center p-6">
                                <div class="bg-zinc-100 dark:bg-zinc-800 p-6 rounded-full inline-block mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-2">No booking URL generated</h3>
                                <div class="h-1 w-24 bg-gradient-to-r from-zinc-300 to-zinc-400 rounded-full mx-auto mb-4"></div>
                                <p class="text-zinc-600 dark:text-zinc-400 mb-6 max-w-xs mx-auto">
                                    Generate a public booking URL to allow customers to book appointments with you online.
                                </p>
                                <div class="mt-4 print:hidden">
                                    <a href="{{ route('settings.public-booking') }}" class="px-5 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg font-medium transition-all duration-300 inline-block shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                        <span class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Generate Booking URL
                                        </span>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const printButton = document.getElementById('printButton');

            if (printButton) {
                printButton.addEventListener('click', function() {
                    window.print();
                });
            }
        });
    </script>

    <!-- Print Styles -->
    <style>
        @media print {
            body {
                background-color: white !important;
                color: black !important;
            }

            /* Hide everything except the div with class "print_this_only" */
            body * {
                display: none !important;
            }

            .print_this_only,
            .print_this_only * {
                display: block !important;
                visibility: visible !important;
            }

            .print_this_only {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 20px !important;
                box-shadow: none !important;
                border: none !important;
            }

            .print\:hidden {
                display: none !important;
            }

            /* Ensure page breaks don't occur within elements */
            .page-break-inside-avoid {
                page-break-inside: avoid;
            }

            /* Force background colors and images to print */
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
            </div>
        </div>
    </div>
</x-settings.layout>
