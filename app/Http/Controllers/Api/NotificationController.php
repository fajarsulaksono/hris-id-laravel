<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = DatabaseNotification::query()
            ->where('notifiable_type', $request->user()::class)
            ->where('notifiable_id', $request->user()->getKey())
            ->latest()
            ->paginate((int) $request->query('ep', 20));

        return NotificationResource::collection($notifications);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = DatabaseNotification::query()
            ->where('notifiable_type', $request->user()::class)
            ->where('notifiable_id', $request->user()->getKey())
            ->findOrFail($id);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return (new NotificationResource($notification->fresh()))->response();
    }
}
