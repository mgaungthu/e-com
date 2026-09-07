<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Api\V1\ChatMessageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /*
        |--------------------------------------------------------------------------
        | Unread Customer Messages
        |--------------------------------------------------------------------------
        |
        | Admin unread count only includes messages sent by the customer.
        |
        | If admin_last_read_at exists:
        | - count customer messages newer than that timestamp.
        |
        | If it does not exist:
        | - count all customer messages.
        |
        */

        $unreadCount = $this->messages()
            ->where('sender_type', 'customer')
            ->when(
                $this->admin_last_read_at,
                fn ($query) => $query->where(
                    'created_at',
                    '>',
                    $this->admin_last_read_at
                )
            )
            ->count();

        return [
            /*
            |--------------------------------------------------------------------------
            | Conversation
            |--------------------------------------------------------------------------
            */

            'id' => $this->id,

            'status' => $this->status,

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            'customer' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,

                    'name' => $this->user->display_name
                        ?: $this->user->name
                        ?: trim(
                            ($this->user->first_name ?? '')
                            . ' '
                            . ($this->user->last_name ?? '')
                        )
                        ?: 'Customer',

                    'email' => $this->user->email,

                    'phone' => $this->user->phone,

                    'avatar_url' => $this->user->avatar_url ?? null,
                ];
            }),

            /*
            |--------------------------------------------------------------------------
            | Latest Message
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | Reuse ChatMessageResource so the latest message has exactly the
            | same shape as messages returned by the conversation endpoint.
            |
            | Frontend expects:
            |
            | sender: {
            |     type,
            |     id,
            |     name,
            |     avatar_url
            | }
            |
            | NOT:
            |
            | sender_type
            | sender_id
            |
            */

            'last_message' => $this->whenLoaded(
                'latestMessage',
                function () {
                    if (!$this->latestMessage) {
                        return null;
                    }

                    return new ChatMessageResource(
                        $this->latestMessage
                    );
                }
            ),

            /*
            |--------------------------------------------------------------------------
            | Unread
            |--------------------------------------------------------------------------
            */

            'unread_count' => $unreadCount,

            /*
            |--------------------------------------------------------------------------
            | Read / Activity Timestamps
            |--------------------------------------------------------------------------
            */

            'admin_last_read_at' =>
                $this->admin_last_read_at?->toISOString(),

            'last_message_at' =>
                $this->last_message_at?->toISOString(),

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            'created_at' =>
                $this->created_at?->toISOString(),

            'updated_at' =>
                $this->updated_at?->toISOString(),
        ];
    }
}