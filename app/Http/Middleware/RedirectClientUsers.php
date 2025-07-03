<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectClientUsers
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

            // Check if user is a client (no role_id or null role)
            if (!$user->role_id && (!isset($user->attributes['role']) || empty($user->attributes['role']) || is_null($user->attributes['role']))) {
                // Get the business slug to redirect to the store
                $businessSlug = $this->getBusinessSlug();

                if ($businessSlug) {
                    return redirect()->route('storefront.index', ['slug' => $businessSlug]);
                }
            }
        }

        return $next($request);
    }

    /**
     * Get the business slug to redirect to.
     *
     * @return string|null
     */
    private function getBusinessSlug(): ?string
    {
        // Try to find a business with store enabled
        $businessDetail = \App\Models\BusinessDetail::where('store_enabled', true)
            ->first();

        return $businessDetail ? $businessDetail->store_slug : null;
    }
}
