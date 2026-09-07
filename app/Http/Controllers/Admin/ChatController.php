<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ChatConversationResource;
use App\Http\Resources\Api\V1\ChatMessageResource;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ChatController extends Controller
{
    /**
     * List all customer conversations for admin/support.
     */
    public function index(Request $request)
    {
        Gate::authorize('chat.view');

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => [
                'nullable',
                'string',
                Rule::in([
                    'open',
                    'closed',
                ]),
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $perPage = $validated['per_page'] ?? 20;

        $conversations = Conversation::query()
            ->with([
                'user',
                'latestMessage',
            ])
            ->when(
                $validated['search'] ?? null,
                function ($query, string $search) {
                    $query->whereHas(
                        'user',
                        function ($userQuery) use ($search) {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        }
                    );
                }
            )
            ->when(
                $validated['status'] ?? null,
                fn ($query, string $status) =>
                    $query->where('status', $status)
            )
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return ChatConversationResource::collection(
            $conversations
        );
    }

    /**
     * Get messages for one conversation.
     */
    public function messages(
        Request $request,
        Conversation $conversation
    ) {
        Gate::authorize('chat.view');

        $validated = $request->validate([
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $perPage = $validated['per_page'] ?? 50;

        $conversation->load('user');

        $messages = $conversation
            ->messages()
            ->with([
                'sender',
                'feed',
                'product',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,

                'status' => $conversation->status,

                'customer' => [
                    'id' => $conversation->user?->id,
                    'name' => $conversation->user?->name,
                    'email' => $conversation->user?->email,
                    'phone' => $conversation->user?->phone,
                    'avatar_url' =>
                        $conversation->user?->avatar_url,
                ],

                'admin_last_read_at' =>
                    $conversation->admin_last_read_at?->toISOString(),

                'last_message_at' =>
                    $conversation->last_message_at?->toISOString(),
            ],

            'messages' => ChatMessageResource::collection(
                $messages
            ),
        ]);
    }

    /**
     * Admin/support sends a message.
     */
    public function storeMessage(
        Request $request,
        Conversation $conversation
    ): JsonResponse {
        Gate::authorize('chat.reply');

        $validated = $request->validate([
            'type' => [
                'required',
                'string',
                Rule::in([
                    'text',
                    'feed',
                    'product',
                ]),
            ],

            'message' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'feed_id' => [
                'nullable',
                'integer',
                'exists:feeds,id',
            ],

            'product_id' => [
                'nullable',
                'integer',
                'exists:products,id',
            ],
        ]);

        if (
            $validated['type'] === 'text'
            && blank($validated['message'] ?? null)
        ) {
            return response()->json([
                'message' => 'Message is required.',
            ], 422);
        }

        $message = $conversation
            ->messages()
            ->create([
                'sender_type' => 'admin',
                'sender_id' => $request->user()->id,

                'type' => $validated['type'],

                'message' =>
                    $validated['message'] ?? null,

                'feed_id' =>
                    $validated['feed_id'] ?? null,

                'product_id' =>
                    $validated['product_id'] ?? null,
            ]);

        $conversation->update([
            'status' => 'open',

            'last_message_at' =>
                $message->created_at,

            'admin_last_read_at' => now(),
        ]);

        $message->load([
            'sender',
            'feed',
            'product',
        ]);

        return response()->json([
            'message' => 'Message sent successfully.',

            'data' => new ChatMessageResource(
                $message
            ),
        ], 201);
    }

    /**
     * Mark customer messages as read by admin.
     */
    public function markAsRead(
        Conversation $conversation
    ): JsonResponse {
        Gate::authorize('chat.view');

        $conversation->update([
            'admin_last_read_at' => now(),
        ]);

        return response()->json([
            'message' =>
                'Conversation marked as read.',

            'data' => [
                'id' => $conversation->id,

                'admin_last_read_at' =>
                    $conversation
                        ->fresh()
                        ->admin_last_read_at
                        ?->toISOString(),
            ],
        ]);
    }
}