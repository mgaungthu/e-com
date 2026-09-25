<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 15);

        $notifications = $request
            ->user()
            ->notifications()
            ->latest()
            ->paginate($perPage);

        $notifications
            ->getCollection()
            ->transform(
                fn (DatabaseNotification $notification): array =>
                    $this->transformNotification($notification)
            );

        return response()->json([
            'success' => true,

            'data' => $notifications,
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request
            ->user()
            ->unreadNotifications()
            ->count();

        return response()->json([
            'success' => true,

            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    public function markAsRead(
        Request $request,
        string $notification
    ): JsonResponse {
        $notificationRecord = $request
            ->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        if ($notificationRecord->read_at === null) {
            $notificationRecord->markAsRead();
        }

        $notificationRecord->refresh();

        return response()->json([
            'success' => true,

            'message' => 'Notification marked as read.',

            'data' => [
                'notification' =>
                    $this->transformNotification(
                        $notificationRecord
                    ),
            ],
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $updated = $request
            ->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,

            'message' => 'All notifications marked as read.',

            'data' => [
                'updated' => $updated,

                'unread_count' => 0,
            ],
        ]);
    }

    private function transformNotification(
        DatabaseNotification $notification
    ): array {
        $payload = $notification->data;

        return [
            'id' => $notification->id,

            'type' => $payload['type']
                ?? 'general',

            'title' => $payload['title']
                ?? '',

            'body' => $payload['body']
                ?? '',

            'data' => $payload['data']
                ?? [],

            'is_read' =>
                $notification->read_at !== null,

            'read_at' =>
                $notification->read_at
                    ?->toISOString(),

            'created_at' =>
                $notification->created_at
                    ?->toISOString(),
        ];
    }
}