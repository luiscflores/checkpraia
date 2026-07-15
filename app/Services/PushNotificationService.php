<?php

namespace App\Services;

use App\Models\Beach;
use App\Models\FlagReport;
use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    private WebPush $webPush;

    public function __construct()
    {
        $auth = [
            'VAPID' => [
                'subject' => config('webpush.vapid.subject'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ];

        $this->webPush = new WebPush($auth);
        $this->webPush->setReuseVAPIDHeaders(true);
    }

    public function notifyBeachVote(Beach $beach, FlagReport $report): void
    {
        $flagName = match ($report->flag) {
            'green' => '🟢 '.__('common.flag_green'),
            'yellow' => '🟡 '.__('common.flag_yellow'),
            'red' => '🔴 '.__('common.flag_red'),
            default => $report->flag,
        };

        $title = __('common.push_vote_title', ['beach' => $beach->name]);
        $body = __('common.push_vote_body', ['flag' => $flagName]);

        $subscribers = PushSubscription::query()
            ->where(function ($q) use ($beach) {
                $q->whereIn('user_id', function ($sub) use ($beach) {
                    $sub->select('user_id')
                        ->from('favorites')
                        ->where('beach_id', $beach->id);
                });

                if ($beach->latitude && $beach->longitude) {
                    $radius = config('webpush.nearby_radius_km', 10);
                    $lat = (float) $beach->latitude;
                    $lng = (float) $beach->longitude;
                    $latDelta = $radius / 111.32;
                    $lngDelta = $radius / (111.32 * cos(deg2rad($lat)));

                    $q->orWhere(function ($near) use ($lat, $lng, $latDelta, $lngDelta) {
                        $near->whereNotNull('latitude')
                            ->whereNotNull('longitude')
                            ->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
                            ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta]);
                    });
                }
            })
            ->get();

        foreach ($subscribers as $sub) {
            if (! $sub->endpoint) {
                continue;
            }

            try {
                $subscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding,
                ]);

                $payload = json_encode([
                    'title' => $title,
                    'body' => $body,
                    'icon' => '/icon-512.png',
                    'badge' => '/icon-192.png',
                    'vibrate' => [200, 100, 200],
                    'data' => [
                        'url' => $beach->url,
                    ],
                ]);

                $this->webPush->queueNotification($subscription, $payload);
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'expired') || str_contains($e->getMessage(), 'gone')) {
                    $sub->delete();
                }
            }
        }

        foreach ($this->webPush->flush() as $reportResult) {
            if (! $reportResult->isSuccess()) {
                $endpoint = $reportResult->getEndpoint();
                PushSubscription::where('endpoint', $endpoint)->delete();
            }
        }
    }

    public function sendTestNotification(PushSubscription $subscription): bool
    {
        try {
            $sub = Subscription::create([
                'endpoint' => $subscription->endpoint,
                'publicKey' => $subscription->public_key,
                'authToken' => $subscription->auth_token,
                'contentEncoding' => $subscription->content_encoding,
            ]);

            $payload = json_encode([
                'title' => 'CheckPraia',
                'body' => 'Notificações ativas! 🎉',
                'icon' => '/icon-512.png',
                'badge' => '/icon-192.png',
                'data' => ['url' => '/'],
            ]);

            $this->webPush->queueNotification($sub, $payload);
            $results = $this->webPush->flush();
            $result = reset($results);

            if ($result && ! $result->isSuccess()) {
                $subscription->delete();

                return false;
            }

            return true;
        } catch (\Exception $e) {
            $subscription->delete();

            return false;
        }
    }
}
