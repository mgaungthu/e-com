<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ChatConversationResource;
use App\Http\Resources\Api\V1\ChatMessageResource;
use App\Models\Conversation;
use App\Models\Product;
use App\Services\Chat\ChatImageService;
use App\Services\Notifications\ChatPushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Throwable;

class AdminChatController extends Controller
{
    /**
     * Get all customer conversations.
     */
    public function index(Request $request)
    {
        Gate::authorize('chat.view');

        $validated =
            $request->validate([
                'search' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

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

        $perPage =
            $validated[
                'per_page'
            ] ?? 20;

        $adminId =
            (int) $request
                ->user()
                ->id;

        /*
        |--------------------------------------------------------------------------
        | Customer Conversations Only
        |--------------------------------------------------------------------------
        |
        | Admin, Super Admin, Product Manager and other staff accounts must
        | never appear inside the customer support inbox.
        |
        */

        $conversations =
            Conversation::query()
                ->whereHas('user', function ($userQuery) {
                    $userQuery->where('role', 'customer');
                })

                /*
                |--------------------------------------------------------------------------
                | Current Admin Star State
                |--------------------------------------------------------------------------
                |
                | Each admin has their own star state.
                |
                */

                ->withExists([
                    'stars as is_starred' =>
                        fn ($query) =>
                            $query->where(
                                'user_id',
                                $adminId
                            ),
                ])

                ->with([
                    'user',
                    'latestMessage.sender',
                    'latestMessage.feed',
                    'latestMessage.product.primaryImage',
                ])

                ->when(
                    $validated[
                        'search'
                    ] ?? null,
                    function (
                        $query,
                        string $search
                    ) {
                        $query->whereHas(
                            'user',
                            function (
                                $userQuery
                            ) use ($search) {
                                $userQuery
                                    ->where(
                                        'role',
                                        'customer'
                                    )
                                    ->where(
                                        function (
                                            $query
                                        ) use ($search) {
                                            $query
                                                ->where(
                                                    'name',
                                                    'like',
                                                    "%{$search}%"
                                                )
                                                ->orWhere(
                                                    'email',
                                                    'like',
                                                    "%{$search}%"
                                                )
                                                ->orWhere(
                                                    'phone',
                                                    'like',
                                                    "%{$search}%"
                                                );
                                        }
                                    );
                            }
                        );
                    }
                )

                ->when(
                    $validated[
                        'status'
                    ] ?? null,
                    fn (
                        $query,
                        string $status
                    ) =>
                        $query->where(
                            'status',
                            $status
                        )
                )

                ->orderByRaw(
                    'CASE
                        WHEN last_message_at IS NULL
                        THEN 1
                        ELSE 0
                    END'
                )

                ->orderByDesc(
                    'last_message_at'
                )

                ->orderByDesc(
                    'id'
                )

                ->paginate(
                    $perPage
                );

        return ChatConversationResource::collection(
            $conversations
        );
    }

    /**
     * Get messages from a specific customer conversation.
     *
     * Supports searching text messages and image captions
     * through the "message" column.
     */
    public function messages(
        Request $request,
        Conversation $conversation
    ): JsonResponse {
        Gate::authorize('chat.view');

        /*
        |--------------------------------------------------------------------------
        | Customer Conversation Guard
        |--------------------------------------------------------------------------
        */

        $this->ensureCustomerConversation(
            $conversation
        );

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'search' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'per_page' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:100',
                ],
            ]);

        $perPage =
            $validated[
                'per_page'
            ] ?? 50;

        $search =
            isset(
                $validated['search']
            )
                ? trim(
                    $validated['search']
                )
                : null;

        /*
        |--------------------------------------------------------------------------
        | Customer
        |--------------------------------------------------------------------------
        */

        $conversation->load([
            'user',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Messages
        |--------------------------------------------------------------------------
        */

        $messages =
            $conversation
                ->messages()
                ->with([
                    'sender',
                    'feed',
                    'product.primaryImage',
                ])

                /*
                |--------------------------------------------------------------------------
                | Search Message Text / Image Caption
                |--------------------------------------------------------------------------
                */

                ->when(
                    filled($search),
                    function ($query) use ($search) {
                        $query->where(
                            'message',
                            'like',
                            "%{$search}%"
                        );
                    }
                )

                /*
                |--------------------------------------------------------------------------
                | Latest First
                |--------------------------------------------------------------------------
                */

                ->orderByDesc(
                    'created_at'
                )

                ->orderByDesc(
                    'id'
                )

                ->paginate(
                    $perPage
                );

        return response()->json([
            'data' => [
                'conversation' => [
                    'id' =>
                        $conversation->id,

                    'status' =>
                        $conversation->status,

                    'customer' => [
                        'id' =>
                            $conversation
                                ->user
                                ?->id,

                        'name' =>
                            $conversation
                                ->user
                                ?->display_name
                            ?: $conversation
                                ->user
                                ?->name,

                        'email' =>
                            $conversation
                                ->user
                                ?->email,

                        'phone' =>
                            $conversation
                                ->user
                                ?->phone,

                        'avatar_url' =>
                            $conversation
                                ->user
                                ?->avatar_url,
                    ],

                    'admin_last_read_at' =>
                        $conversation
                            ->admin_last_read_at
                            ?->toISOString(),

                    'last_message_at' =>
                        $conversation
                            ->last_message_at
                            ?->toISOString(),

                    'created_at' =>
                        $conversation
                            ->created_at
                            ?->toISOString(),
                ],

                'messages' =>
                    ChatMessageResource::collection(
                        $messages
                    )
                        ->response()
                        ->getData(true),
            ],
        ]);
    }

    /**
     * Send a message to a customer.
     */
    public function storeMessage(
        Request $request,
        Conversation $conversation,
        ChatPushNotificationService $chatPush,
        ChatImageService $chatImageService
    ): JsonResponse {
        Gate::authorize('chat.reply');

        /*
        |--------------------------------------------------------------------------
        | Customer Conversation Guard
        |--------------------------------------------------------------------------
        */

        $this->ensureCustomerConversation(
            $conversation
        );

        /*
        |--------------------------------------------------------------------------
        | Normalize Message
        |--------------------------------------------------------------------------
        */

        if ($request->has('message')) {
            $message =
                $request->input('message');

            $request->merge([
                'message' =>
                    is_string($message)
                        ? trim($message)
                        : $message,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'type' => [
                    'required',
                    'string',
                    Rule::in([
                        'text',
                        'image',
                        'feed',
                        'product',
                    ]),
                ],

                /*
                 * Required for text messages.
                 * Optional caption for image messages.
                 */
                'message' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],

                'image' => [
                    'nullable',
                    'file',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:8192',
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

        /*
        |--------------------------------------------------------------------------
        | Type Specific Validation
        |--------------------------------------------------------------------------
        */

        if (
            $validated['type'] === 'text'
            && blank(
                $validated['message'] ?? null
            )
        ) {
            return response()->json([
                'message' =>
                    'Message is required.',

                'errors' => [
                    'message' => [
                        'Message is required for text messages.',
                    ],
                ],
            ], 422);
        }

        if (
            $validated['type'] === 'image'
            && ! $request->hasFile('image')
        ) {
            return response()->json([
                'message' =>
                    'Image is required.',

                'errors' => [
                    'image' => [
                        'Image is required for image messages.',
                    ],
                ],
            ], 422);
        }

        if (
            $validated['type'] === 'feed'
            && empty(
                $validated['feed_id']
            )
        ) {
            return response()->json([
                'message' =>
                    'Feed is required.',

                'errors' => [
                    'feed_id' => [
                        'Feed is required for feed messages.',
                    ],
                ],
            ], 422);
        }

        if (
            $validated['type'] === 'product'
            && empty(
                $validated['product_id']
            )
        ) {
            return response()->json([
                'message' =>
                    'Product is required.',

                'errors' => [
                    'product_id' => [
                        'Product is required for product messages.',
                    ],
                ],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Active Product
        |--------------------------------------------------------------------------
        */

        if (
            $validated['type'] === 'product'
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
                $validated['type'] === 'image'
            ) {
                $image =
                    $request->file('image');

                if (! $image) {
                    return response()->json([
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
                            $conversation->id
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
                        $conversation,
                        $validated,
                        $imageData
                    ) {
                        $message =
                            $conversation
                                ->messages()
                                ->create([
                                    'sender_type' =>
                                        'admin',

                                    'sender_id' =>
                                        $request
                                            ->user()
                                            ->id,

                                    'type' =>
                                        $validated[
                                            'type'
                                        ],

                                    /*
                                     * Text content or image caption.
                                     */
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
                                $message
                                    ->created_at,

                            'admin_last_read_at' =>
                                now(),
                        ]);

                        return $message;
                    }
                );
        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Cleanup Orphan Image
            |--------------------------------------------------------------------------
            */

            if (
                ! empty(
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
            'sender',
            'feed',
            'product.primaryImage',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Load Customer
        |--------------------------------------------------------------------------
        */

        $conversation->loadMissing(
            'user'
        );

        /*
        |--------------------------------------------------------------------------
        | Push Notification
        |--------------------------------------------------------------------------
        */

        $chatPush->notifyCustomer(
            $conversation,
            $message
        );

        return response()->json([
            'message' =>
                'Message sent successfully.',

            'data' =>
                new ChatMessageResource(
                    $message
                ),
        ], 201);
    }

    /**
     * Mark customer messages in conversation as read.
     */
    public function markAsRead(
        Conversation $conversation
    ): JsonResponse {
        Gate::authorize('chat.view');

        /*
        |--------------------------------------------------------------------------
        | Customer Conversation Guard
        |--------------------------------------------------------------------------
        */

        $this->ensureCustomerConversation(
            $conversation
        );

        $conversation->update([
            'admin_last_read_at' =>
                now(),
        ]);

        $conversation->refresh();

        return response()->json([
            'message' =>
                'Conversation marked as read.',

            'data' => [
                'id' =>
                    $conversation->id,

                'admin_last_read_at' =>
                    $conversation
                        ->admin_last_read_at
                        ?->toISOString(),
            ],
        ]);
    }

    /**
     * Star a customer conversation for the authenticated admin.
     */
    public function star(
        Request $request,
        Conversation $conversation
    ): JsonResponse {
        Gate::authorize('chat.view');

        /*
        |--------------------------------------------------------------------------
        | Customer Conversation Guard
        |--------------------------------------------------------------------------
        */

        $this->ensureCustomerConversation(
            $conversation
        );

        /*
        |--------------------------------------------------------------------------
        | Store Star
        |--------------------------------------------------------------------------
        */

        $conversation
            ->stars()
            ->firstOrCreate([
                'user_id' =>
                    $request
                        ->user()
                        ->id,
            ]);

        return response()->json([
            'message' =>
                'Conversation starred successfully.',

            'data' => [
                'id' =>
                    $conversation->id,

                'is_starred' =>
                    true,
            ],
        ]);
    }

    /**
     * Remove the authenticated admin's star
     * from a customer conversation.
     */
    public function unstar(
        Request $request,
        Conversation $conversation
    ): JsonResponse {
        Gate::authorize('chat.view');

        /*
        |--------------------------------------------------------------------------
        | Customer Conversation Guard
        |--------------------------------------------------------------------------
        */

        $this->ensureCustomerConversation(
            $conversation
        );

        /*
        |--------------------------------------------------------------------------
        | Delete Star
        |--------------------------------------------------------------------------
        */

        $conversation
            ->stars()
            ->where(
                'user_id',
                $request
                    ->user()
                    ->id
            )
            ->delete();

        return response()->json([
            'message' =>
                'Conversation unstarred successfully.',

            'data' => [
                'id' =>
                    $conversation->id,

                'is_starred' =>
                    false,
            ],
        ]);
    }

    /**
     * Ensure admin support endpoints are only used
     * with customer conversations.
     */
    private function ensureCustomerConversation(
        Conversation $conversation
    ): void {
        $conversation->loadMissing(
            'user'
        );

        abort_unless(
            $conversation->user
                && $conversation
                    ->user
                    ->role === 'customer',
            404
        );
    }
}