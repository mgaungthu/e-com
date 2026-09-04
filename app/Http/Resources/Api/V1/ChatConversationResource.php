<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lastMessageAt =
            $this->last_message_at;

        $userLastReadAt =
            $this->user_last_read_at;

        $unreadCount = $this
            ->messages()
            ->where(
                'sender_type',
                'admin',
            )
            ->when(
                $userLastReadAt,
                fn ($query) => $query->where(
                    'created_at',
                    '>',
                    $userLastReadAt,
                ),
            )
            ->count();

        return [
            'id' => $this->id,

            'status' => $this->status,

            'last_message_at' =>
                $lastMessageAt?->toISOString(),

            'user_last_read_at' =>
                $userLastReadAt?->toISOString(),

            'unread_count' =>
                $unreadCount,

            'last_message' => $this->whenLoaded(
                'latestMessage',
                fn () => $this->latestMessage
                    ? new ChatMessageResource(
                        $this->latestMessage,
                    )
                    : null,
            ),

            'created_at' =>
                $this->created_at?->toISOString(),

            'updated_at' =>
                $this->updated_at?->toISOString(),
        ];
    }
}