<?php

namespace App\Notifications\Channels;

use App\Models\DeviceToken;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Throwable;

class FcmChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $payload = $notification->toFcm($notifiable);
        $tokens = DeviceToken::query()
            ->where('employee_id', $notifiable->getKey())
            ->pluck('token')
            ->all();

        $credentials = config('services.firebase.credentials');
        if ($tokens === [] || ! is_string($credentials) || $credentials === '') {
            return;
        }

        try {
            $messaging = (new Factory)
                ->withServiceAccount($credentials)
                ->createMessaging();
            $message = CloudMessage::new()
                ->withNotification([
                    'title' => $payload['title'],
                    'body' => $payload['body'],
                ])
                ->withData($payload['data'])
                ->withHighestPossiblePriority();

            $messaging->sendMulticast($message, $tokens);
        } catch (Throwable $exception) {
            Log::warning('FCM notification delivery failed.', [
                'employee_id' => $notifiable->getKey(),
                'exception' => $exception,
            ]);
        }
    }
}
