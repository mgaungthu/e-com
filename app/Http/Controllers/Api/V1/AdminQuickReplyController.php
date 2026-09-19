<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\QuickReplyResource;
use App\Models\QuickReply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminQuickReplyController extends Controller
{
    /**
     * Get quick replies.
     */
    public function index(
        Request $request
    ): JsonResponse {
        Gate::authorize(
            'chat.reply'
        );

        $validated =
            $request->validate([
                'active' => [
                    'nullable',
                    'boolean',
                ],
            ]);

        $quickReplies =
            QuickReply::query()
                ->when(
                    array_key_exists(
                        'active',
                        $validated
                    ),
                    fn ($query) =>
                        $query->where(
                            'is_active',
                            $validated['active']
                        )
                )
                ->orderBy(
                    'sort_order'
                )
                ->orderBy(
                    'id'
                )
                ->get();

        return response()->json([
            'data' =>
                QuickReplyResource::collection(
                    $quickReplies
                ),
        ]);
    }

    /**
     * Create a quick reply.
     */
    public function store(
        Request $request
    ): JsonResponse {
        Gate::authorize(
            'chat.reply'
        );

        $validated =
            $request->validate([
                'title' => [
                    'required',
                    'string',
                    'max:120',
                ],

                'message' => [
                    'required',
                    'string',
                    'max:5000',
                ],

                'is_active' => [
                    'nullable',
                    'boolean',
                ],

                'sort_order' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],
            ]);

        $quickReply =
            QuickReply::create([
                'title' =>
                    $validated['title'],

                'message' =>
                    $validated['message'],

                'is_active' =>
                    $validated[
                        'is_active'
                    ] ?? true,

                'sort_order' =>
                    $validated[
                        'sort_order'
                    ] ?? 0,

                'created_by' =>
                    $request
                        ->user()
                        ->id,
            ]);

        return response()->json([
            'message' =>
                'Quick reply created successfully.',

            'data' =>
                new QuickReplyResource(
                    $quickReply
                ),
        ], 201);
    }

    /**
     * Update a quick reply.
     */
    public function update(
        Request $request,
        QuickReply $quickReply,
    ): JsonResponse {
        Gate::authorize(
            'chat.reply'
        );

        $validated =
            $request->validate([
                'title' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:120',
                ],

                'message' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:5000',
                ],

                'is_active' => [
                    'sometimes',
                    'boolean',
                ],

                'sort_order' => [
                    'sometimes',
                    'integer',
                    'min:0',
                ],
            ]);

        $quickReply->update(
            $validated
        );

        $quickReply->refresh();

        return response()->json([
            'message' =>
                'Quick reply updated successfully.',

            'data' =>
                new QuickReplyResource(
                    $quickReply
                ),
        ]);
    }

    /**
     * Delete a quick reply.
     */
    public function destroy(
        QuickReply $quickReply,
    ): JsonResponse {
        Gate::authorize(
            'chat.reply'
        );

        $quickReply->delete();

        return response()->json([
            'message' =>
                'Quick reply deleted successfully.',
        ]);
    }
}