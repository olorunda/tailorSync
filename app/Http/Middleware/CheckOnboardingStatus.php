<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboardingStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::check()) {


            $user = Auth::user();

            // Check if user needs onboarding
            if ($user->needsOnboarding()) {
                // If the user is not already on the onboarding route, redirect them
                if (!$request->routeIs('onboarding.*')) {
                    return redirect()->route('onboarding.wizard');
                }
            }
        }

        return $next($request);
    }
}
