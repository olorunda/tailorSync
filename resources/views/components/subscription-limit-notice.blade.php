@props(['feature' => null, 'plan' => null, 'message' => null])

@php
    $user = auth()->user();
    $businessDetail = $user->businessDetail;
    $currentPlan = $businessDetail->subscription_plan ?? 'free';
    $isActive = \App\Services\SubscriptionService::isActive($businessDetail);

    // Get all plans for comparison
    $plans = \App\Services\SubscriptionService::getPlans();

    // If no specific plan is provided, suggest the next tier up
  //  if (!$plan) {

        if ($currentPlan === 'free') {
            $plan = 'basic';
        } elseif ($currentPlan === 'basic') {
            $plan = 'premium';
        } else {
            $plan = 'premium'; // Already at highest tier
        }
   // }
//    dd($plan);

    // Get the suggested plan details
    $suggestedPlan = $plans[$plan] ?? null;

    // Default message if none provided
    if (!$message) {
        if ($feature) {
            $message = "Your current subscription plan does not include the {$feature} feature.";
        } else {
            $message = "You've reached a limit in your current subscription plan.";
        }
    }
@endphp

<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3">
            <p class="text-sm text-yellow-700">
                {{ $message }}
                @if ($suggestedPlan)
                    <span class="font-medium">Upgrade to the {{ $suggestedPlan['name'] }} plan (₦{{ number_format($suggestedPlan['price']) }}/month) to access this feature.</span>
                @endif
            </p>
            <p class="mt-2">
                <a href="{{ route('subscriptions.index') }}" class="text-sm font-medium text-yellow-700 hover:text-yellow-600">
                    View subscription plans <span aria-hidden="true">&rarr;</span>
                </a>
            </p>
        </div>
    </div>
</div>
