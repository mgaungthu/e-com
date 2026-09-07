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
use App\Services\Chat\ChatImageService;
use App\Services\Notifications\ChatPushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ChatController extends Controller
{
    /**
     * Return the authenticated customer's support conversation.
     *
     * The first request automatically creates the conversation.
     */
    public function show(
        Request $request
    ): JsonResponse {
        $conversation =
            $this->conversationForUser(
                $request
            );

        $conversation->load([
            'latestMessage.sender',
            'latestMessage.feed',
            'latestMessage.product.primaryImage',
        ]);

        return response()->json([
            'success' => true,

            'data' => [
                'conversation' =>
                    new ChatConversationResource(
                        $conversation
                    ),
            ],
        ]);
    }

    /**
     * Return messages for the authenticated customer's conversation.
     */
    public function messages(
        Request $request
    ): JsonResponse {
        $conversation =
            $this->conversationForUser(
                $request
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

        $messages =
            $conversation
                ->messages()
                ->with([
                    'sender:id,name,display_name,avatar_path',
                    'feed',
                    'product.primaryImage',
                ])
                ->orderByDesc('id')
                ->paginate(
                    $perPage
                );

        return response()->json([
            'success' => true,

            'data' => [
                'messages' =>
                    ChatMessageResource::collection(
                        $messages->getCollection()
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
        ChatPushNotificationService $chatPush,
        ChatImageService $chatImageService,
    ): JsonResponse {
        $validated =
            $request->validated();

        $conversation =
            $this->conversationForUser(
                $request
            );

        /*
        |--------------------------------------------------------------------------
        | Validate Shared Feed
        |--------------------------------------------------------------------------
        */

        if (
            $validated['type'] ===
            'feed'
        ) {
            Feed::query()
                ->visible()
                ->findOrFail(
                    $validated['feed_id']
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Shared Product
        |--------------------------------------------------------------------------
        */

        if (
            $validated['type'] ===
            'product'
        ) {
            Product::query()
                ->where(
                    'is_active',
                    true
                )
                ->findOrFail(
                    $validated[
                        'product_id'
                    ]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Store Image
        |--------------------------------------------------------------------------
        */

        $imageData = [];

        try {
            if (
                $validated['type'] ===
                'image'
            ) {
                $image =
                    $request->file(
                        'image'
                    );

                if (!$image) {
                    return response()->json([
                        'success' => false,

                        'message' =>
                            'Image is required.',

                        'errors' => [
                            'image' => [
                                'Image is required for image messages.',
                            ],
                        ],
                    ], 422);
                }

                $imageData =
                    $chatImageService
                        ->store(
                            $image,
                            $conversation->id,
                        );
            }

            /*
            |--------------------------------------------------------------------------
            | Create Message
            |--------------------------------------------------------------------------
            */

            $message =
                DB::transaction(
                    function () use (
                        $request,
                        $validated,
                        $conversation,
                        $imageData,
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

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Image
                                    |--------------------------------------------------------------------------
                                    */

                                    'image_path' =>
                                        $imageData[
                                            'image_path'
                                        ] ?? null,

                                    'image_width' =>
                                        $imageData[
                                            'image_width'
                                        ] ?? null,

                                    'image_height' =>
                                        $imageData[
                                            'image_height'
                                        ] ?? null,

                                    'image_size' =>
                                        $imageData[
                                            'image_size'
                                        ] ?? null,

                                    'image_mime_type' =>
                                        $imageData[
                                            'image_mime_type'
                                        ] ?? null,

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Shared Resources
                                    |--------------------------------------------------------------------------
                                    */

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
                        | Conversation State
                        |--------------------------------------------------------------------------
                        */

                        $conversation->update([
                            'status' =>
                                'open',

                            'last_message_at' =>
                                $message->created_at,

                            'user_last_read_at' =>
                                now(),
                        ]);

                        return $message;
                    },
                );
        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Cleanup Uploaded Image
            |--------------------------------------------------------------------------
            */

            if (
                !empty(
                    $imageData[
                        'image_path'
                    ]
                )
            ) {
                $chatImageService
                    ->delete(
                        $imageData[
                            'image_path'
                        ]
                    );
            }

            throw $exception;
        }

        /*
        |--------------------------------------------------------------------------
        | Load Message Relations
        |--------------------------------------------------------------------------
        */

        $message->load([
            'sender:id,name,display_name,avatar_path',
            'feed',
            'product.primaryImage',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Push Notification
        |--------------------------------------------------------------------------
        */

        $chatPush->notifyAdmins(
            $conversation,
            $message,
            $request->user(),
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Message sent successfully.',

            'data' => [
                'message' =>
                    new ChatMessageResource(
                        $message
                    ),
            ],
        ], 201);
    }

    /**
     * Mark the conversation as read by the customer.
     */
    public function markAsRead(
        Request $request
    ): JsonResponse {
        $conversation =
            $this->conversationForUser(
                $request
            );

        $conversation->update([
            'user_last_read_at' =>
                now(),
        ]);

        $conversation->load([
            'latestMessage.sender',
            'latestMessage.feed',
            'latestMessage.product.primaryImage',
        ]);

        return response()->json([
            'success' => true,

            'message' =>
                'Conversation marked as read.',

            'data' => [
                'conversation' =>
                    new ChatConversationResource(
                        $conversation
                    ),
            ],
        ]);
    }

    /**
     * Resolve authenticated customer's support conversation.
     */
    private function conversationForUser(
        Request $request
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
                    'status' =>
                        'open',

                    'user_last_read_at' =>
                        now(),
                ],
            );
    }
}