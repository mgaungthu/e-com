<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Chat\StoreChatMessageRequest;
use App\Http\Resources\Api\V1\ChatConversationResource;
use App\Http\Resources\Api\V1\ChatMessageResource;
use App\Models\Conversation;
use App\Models\Feed;
use App\Models\Message;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Return the authenticated customer's support conversation.
     *
     * The first request automatically creates the conversation.
     */
    public function show(Request $request): JsonResponse
    {
        $conversation = $this->conversationForUser(
            $request,
        );

        $conversation->load([
            'latestMessage.sender',
            'latestMessage.feed',
            'latestMessage.product',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'conversation' =>
                    new ChatConversationResource(
                        $conversation,
                    ),
            ],
        ]);
    }

    /**
     * Return messages for the authenticated customer's conversation.
     */
    public function messages(Request $request): JsonResponse
    {
        $conversation = $this->conversationForUser(
            $request,
        );

        $perPage = min(
            max(
                $request->integer(
                    'per_page',
                    50,
                ),
                1,
            ),
            100,
        );

        $messages = $conversation
            ->messages()
            ->with([
                'sender:id,name,display_name,avatar_path',
                'feed',
                'product',
            ])
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,

            'data' => [
                'messages' =>
                    ChatMessageResource::collection(
                        $messages->getCollection(),
                    ),

                'meta' => [
                    'current_page' =>
                        $messages->currentPage(),

                    'last_page' =>
                        $messages->lastPage(),

                    'per_page' =>
                        $messages->perPage(),

                    'total' =>
                        $messages->total(),
                ],
            ],
        ]);
    }

    /**
     * Store a customer message.
     */
    public function storeMessage(
        StoreChatMessageRequest $request,
    ): JsonResponse {
        $validated =
            $request->validated();

        $conversation =
            $this->conversationForUser(
                $request,
            );

        /*
        |--------------------------------------------------------------------------
        | Validate shared resources
        |--------------------------------------------------------------------------
        |
        | exists validation confirms the row exists.
        | These extra checks make sure customers cannot share hidden feeds or
        | inactive products into the support chat.
        |--------------------------------------------------------------------------
        */

        if (
            $validated['type'] ===
            'feed'
        ) {
            Feed::query()
                ->visible()
                ->findOrFail(
                    $validated['feed_id'],
                );
        }

        if (
            $validated['type'] ===
            'product'
        ) {
            Product::query()
                ->where(
                    'is_active',
                    true,
                )
                ->findOrFail(
                    $validated['product_id'],
                );
        }

        $message = DB::transaction(
            function () use (
                $request,
                $validated,
                $conversation,
            ): Message {
                $message =
                    $conversation
                        ->messages()
                        ->create([
                            'sender_type' =>
                                'customer',

                            'sender_id' =>
                                $request
                                    ->user()
                                    ->id,

                            'type' =>
                                $validated[
                                    'type'
                                ],

                            'message' =>
                                $validated[
                                    'message'
                                ] ?? null,

                            'feed_id' =>
                                $validated[
                                    'feed_id'
                                ] ?? null,

                            'product_id' =>
                                $validated[
                                    'product_id'
                                ] ?? null,
                        ]);

                /*
                |--------------------------------------------------------------------------
                | Update conversation state
                |--------------------------------------------------------------------------
                |
                | Sending a message means:
                | - conversation is active/open
                | - this is now the latest message
                | - customer has read everything up to this moment
                |--------------------------------------------------------------------------
                */

                $conversation->update([
                    'status' => 'open',

                    'last_message_at' =>
                        $message->created_at,

                    'user_last_read_at' =>
                        now(),
                ]);

                return $message;
            },
        );

        $message->load([
            'sender:id,name,display_name,avatar_path',
            'feed',
            'product',
        ]);

        return response()->json([
            'success' => true,

            'message' =>
                'Message sent successfully.',

            'data' => [
                'message' =>
                    new ChatMessageResource(
                        $message,
                    ),
            ],
        ], 201);
    }

    /**
     * Mark the conversation as read by the customer.
     */
    public function markAsRead(
        Request $request,
    ): JsonResponse {
        $conversation =
            $this->conversationForUser(
                $request,
            );

        $conversation->update([
            'user_last_read_at' =>
                now(),
        ]);

        $conversation->load([
            'latestMessage.sender',
            'latestMessage.feed',
            'latestMessage.product',
        ]);

        return response()->json([
            'success' => true,

            'message' =>
                'Conversation marked as read.',

            'data' => [
                'conversation' =>
                    new ChatConversationResource(
                        $conversation,
                    ),
            ],
        ]);
    }

    /**
     * Resolve the authenticated customer's support conversation.
     *
     * Customers must never be able to access another user's conversation
     * by supplying a conversation ID.
     */
    private function conversationForUser(
        Request $request,
    ): Conversation {
        return Conversation::query()
            ->firstOrCreate(
                [
                    'user_id' =>
                        $request
                            ->user()
                            ->id,
                ],
                [
                    'status' => 'open',

                    'user_last_read_at' =>
                        now(),
                ],
            );
    }
}