<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionRestrictionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature = null): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $businessDetail = $user->businessDetail;

        // If no business detail, redirect to onboarding
        if (!$businessDetail) {
            return redirect()->route('onboarding.wizard');
        }

        // If no specific feature is required, just check if subscription is active
        if (!$feature) {
            if (!SubscriptionService::isActive($businessDetail)) {
                return redirect()->route('subscriptions.index')
                    ->with('error', 'Your subscription is inactive. Please renew your subscription to continue using this feature.');
            }

            return $next($request);
        }

        // Check if the user's subscription plan allows the requested feature
        if (!SubscriptionService::canUseFeature($businessDetail, $feature)) {
            $planKey = $businessDetail->subscription_plan ?? 'free';

            return redirect()->route('subscriptions.index')
                ->with('error', "Your current {$planKey} plan does not include the {$feature} feature. Please upgrade your plan to access it.")
                ->with('subscription_limit_reached', true)
                ->with('subscription_feature', $feature)
                ->with('subscription_plan', $planKey);
        }

        return $next($request);
    }
}
