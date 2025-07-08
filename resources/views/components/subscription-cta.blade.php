@php
    // Get the current user's business details
    $businessDetail = auth()->user()->businessDetail;

    // Check if the user has a paid subscription
    $hasPaidSubscription = $businessDetail && $businessDetail->subscription_plan !== 'free' &&
                           \App\Services\SubscriptionService::isActive($businessDetail);

    // If user has a paid subscription, don't show the CTA
    if ($hasPaidSubscription) {
        return;
    }

    // Collection of attractive CTA prompts
    $prompts = [
        [
            'title' => '🚀 Unlock Your Full Potential!',
            'message' => 'Upgrade to a paid plan and access unlimited designs, custom domain, and more features to grow your business.',
            'color' => 'blue'
        ],
        [
            'title' => '💎 Ready to Stand Out?',
            'message' => 'Premium users enjoy AI style suggestions and unlimited team members. Elevate your business today!',
            'color' => 'purple'
        ],
        [
            'title' => '🔥 Limited Time Offer!',
            'message' => 'Upgrade now and transform your business with our powerful tools and features designed for success.',
            'color' => 'amber'
        ],
        [
            'title' => '⚡ Boost Your Productivity!',
            'message' => 'Our paid plans include advanced features that save you time and help you manage your business more efficiently.',
            'color' => 'emerald'
        ],
        [
            'title' => '🌟 Join Our Premium Users!',
            'message' => 'Thousands of businesses have upgraded to unlock the full power of TailorFit. Don\'t miss out!',
            'color' => 'rose'
        ]
    ];

    // Randomly select a prompt
    $prompt = $prompts[array_rand($prompts)];

    // Set color classes based on the prompt's color
    $colorClasses = [
        'blue' => [
            'bg' => 'bg-blue-500',
            'text' => 'text-white',
            'hover' => 'hover:bg-blue-600',
            'ring' => 'focus:ring-blue-500'
        ],
        'purple' => [
            'bg' => 'bg-purple-500',
            'text' => 'text-white',
            'hover' => 'hover:bg-purple-600',
            'ring' => 'focus:ring-purple-500'
        ],
        'amber' => [
            'bg' => 'bg-amber-500',
            'text' => 'text-white',
            'hover' => 'hover:bg-amber-600',
            'ring' => 'focus:ring-amber-500'
        ],
        'emerald' => [
            'bg' => 'bg-emerald-500',
            'text' => 'text-white',
            'hover' => 'hover:bg-emerald-600',
            'ring' => 'focus:ring-emerald-500'
        ],
        'rose' => [
            'bg' => 'bg-rose-500',
            'text' => 'text-white',
            'hover' => 'hover:bg-rose-600',
            'ring' => 'focus:ring-rose-500'
        ]
    ];

    $colorClass = $colorClasses[$prompt['color']] ?? $colorClasses['blue'];
@endphp

@unless($hasPaidSubscription)
<div x-data="{
    show: false,
    title: '{{ $prompt['title'] }}',
    message: '{{ $prompt['message'] }}',
    timer: null,
    init() {
        // Check if the toast has been shown in the last hour
        const lastShown = localStorage.getItem('subscription_cta_last_shown');
        const currentTime = new Date().getTime();
        const oneHour = 60 * 60 * 1000; // 1 hour in milliseconds

        if (!lastShown || (currentTime - parseInt(lastShown)) > oneHour) {
            // If it hasn't been shown in the last hour, show it and update the timestamp
            this.show = true;
            localStorage.setItem('subscription_cta_last_shown', currentTime.toString());

            // Auto-hide after 8 seconds
            this.timer = setTimeout(() => {
                this.show = false;
            }, 8000);
        }
    },
    close() {
        this.show = false;
        clearTimeout(this.timer);
    }
}"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform translate-y-2"
    class="fixed bottom-4 right-4 z-50 max-w-md w-full"
    @click.away="close()"
    style="display: none;">

    <div class="rounded-lg shadow-lg overflow-hidden {{ $colorClass['bg'] }} {{ $colorClass['text'] }}">
        <div class="p-5">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-7 w-7 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-base font-bold leading-tight" x-text="title"></p>
                    <p class="mt-2 text-base leading-relaxed" x-text="message"></p>
                    <div class="mt-4 flex space-x-4">
                        <a href="{{ route('subscriptions.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm bg-white text-gray-800 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $colorClass['ring'] }}"
                           wire:navigate>
                            Upgrade Now
                        </a>
                        <button @click="close()" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white hover:text-gray-200 focus:outline-none">
                            Dismiss
                        </button>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button @click="close()" class="inline-flex text-white hover:text-gray-200 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endunless
