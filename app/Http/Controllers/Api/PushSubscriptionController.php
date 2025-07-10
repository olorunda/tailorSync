<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PushSubscriptionController extends Controller
{
    /**
     * Store a newly created push subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'endpoint' => 'required|string|max:500',
            'keys.p256dh' => 'required|string|max:255',
            'keys.auth' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check if subscription already exists
        if ($user->hasPushSubscription($request->endpoint)) {
            return response()->json(['message' => 'Subscription already exists'], 200);
        }

        // Create new subscription
        $subscription = $user->createPushSubscription([
            'endpoint' => $request->endpoint,
            'keys' => [
                'p256dh' => $request->keys['p256dh'],
                'auth' => $request->keys['auth'],
            ],
            'contentEncoding' => $request->contentEncoding ?? 'aes128gcm',
        ]);

        return response()->json([
            'message' => 'Subscription created successfully',
            'subscription' => $subscription
        ], 201);
    }

    /**
     * Remove the specified push subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'endpoint' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Delete subscription
        $deleted = $user->deletePushSubscription($request->endpoint);

        if ($deleted) {
            return response()->json(['message' => 'Subscription deleted successfully'], 200);
        }

        return response()->json(['message' => 'Subscription not found'], 404);
    }
}
