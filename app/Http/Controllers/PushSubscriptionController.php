<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
            'public_key' => 'nullable|string',
            'auth_token' => 'nullable|string',
            'content_encoding' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $subscription = PushSubscription::updateOrCreate(
            ['endpoint' => $request->endpoint],
            [
                'user_id' => auth()->id(),
                'public_key' => $request->public_key,
                'auth_token' => $request->auth_token,
                'content_encoding' => $request->content_encoding ?? 'aesgcm',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'subscribed_at' => now(),
            ]
        );

        return response()->json(['success' => true, 'id' => $subscription->id]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => 'required|string']);

        PushSubscription::where('endpoint', $request->endpoint)->delete();

        return response()->json(['success' => true]);
    }

    public function status(): JsonResponse
    {
        $subscriptions = auth()->check()
            ? PushSubscription::where('user_id', auth()->id())->get()
            : collect();

        return response()->json([
            'subscribed' => $subscriptions->isNotEmpty(),
            'has_location' => $subscriptions->whereNotNull('latitude')->isNotEmpty(),
            'count' => $subscriptions->count(),
        ]);
    }

    public function test(Request $request, PushNotificationService $push): JsonResponse
    {
        $request->validate(['endpoint' => 'required|string']);

        $subscription = PushSubscription::where('endpoint', $request->endpoint)->first();

        if (!$subscription) {
            return response()->json(['success' => false, 'error' => 'Subscription not found'], 404);
        }

        $sent = $push->sendTestNotification($subscription);

        return response()->json(['success' => $sent]);
    }
}
