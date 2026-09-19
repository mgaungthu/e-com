<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Feed\StoreFeedRequest;
use App\Http\Requests\Admin\Feed\UpdateFeedRequest;
use App\Http\Requests\Api\V1\Feed\StoreFeedCommentRequest;
use App\Http\Resources\Api\V1\FeedCommentResource;
use App\Http\Resources\Api\V1\FeedResource;
use App\Models\Feed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FeedController extends Controller
{
    public function store(StoreFeedRequest $request): JsonResponse
    {
        $data = $request->validated();

        $feed = DB::transaction(function () use ($request, $data): Feed {
            $feed = Feed::query()->create([
                'user_id' => $request->user()->id,
                'caption' => $data['caption'] ?? null,
                'status' => $data['status'],
                'is_active' => $request->boolean('is_active'),
                'published_at' => $data['published_at'] ?? null,
            ]);

            foreach ($data['products'] ?? [] as $index => $product) {
                $feed->products()->attach(
                    $product['product_id'],
                    [
                        'sort_order' =>
                            $product['sort_order']
                            ?? $index,
                    ]
                );
            }

            foreach ($request->file('images', []) as $index => $image) {
                $feed->media()->create([
                    'type' => 'image',
                    'file_path' =>
                        $image->store(
                            'feeds',
                            'public'
                        ),
                    'sort_order' => $index,
                ]);
            }

            return $feed;
        });

        return response()->json([
            'success' => true,
            'message' =>
                'Feed post created successfully.',
            'data' => [
                'feed' =>
                    new FeedResource(
                        $this
                            ->visibleQuery($request)
                            ->findOrFail(
                                $feed->id
                            )
                    ),
            ],
        ], 201);
    }

    public function update(
        UpdateFeedRequest $request,
        Feed $feed
    ): JsonResponse {
        $data =
            $request->validated();

        $removedMediaPaths = [];

        DB::transaction(function () use (
            $request,
            $data,
            $feed,
            &$removedMediaPaths
        ): void {
            /*
            |--------------------------------------------------------------------------
            | Update Feed
            |--------------------------------------------------------------------------
            */

            $feed->update([
                'caption' =>
                    $data['caption'] ?? null,

                'status' =>
                    $data['status'],

                'is_active' =>
                    $request->boolean(
                        'is_active'
                    ),

                'published_at' =>
                    $data['published_at'] ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Sync Products
            |--------------------------------------------------------------------------
            */

            $products = [];

            foreach (
                $data['products'] ?? []
                as $index => $product
            ) {
                $products[
                    $product['product_id']
                ] = [
                    'sort_order' =>
                        $product['sort_order']
                        ?? $index,
                ];
            }

            $feed
                ->products()
                ->sync($products);

            /*
            |--------------------------------------------------------------------------
            | Remove Media
            |--------------------------------------------------------------------------
            */

            $removeMediaIds =
                collect(
                    $data[
                        'remove_media_ids'
                    ] ?? []
                )
                    ->map(
                        fn ($id) =>
                            (int) $id
                    )
                    ->values();

            if (
                $removeMediaIds
                    ->isNotEmpty()
            ) {
                $mediaToRemove =
                    $feed
                        ->media()
                        ->whereIn(
                            'id',
                            $removeMediaIds
                        )
                        ->get();

                foreach (
                    $mediaToRemove
                    as $media
                ) {
                    if (
                        $media->file_path
                    ) {
                        $removedMediaPaths[] =
                            $media->file_path;
                    }

                    $media->delete();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Existing Media Order
            |--------------------------------------------------------------------------
            */

            $mediaOrder =
                collect(
                    $data[
                        'media_order'
                    ] ?? []
                )
                    ->map(
                        fn ($id) =>
                            (int) $id
                    )
                    ->values();

            foreach (
                $mediaOrder
                as $index => $mediaId
            ) {
                $feed
                    ->media()
                    ->where(
                        'id',
                        $mediaId
                    )
                    ->update([
                        'sort_order' =>
                            $index,
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Append New Images
            |--------------------------------------------------------------------------
            */

            $existingMediaCount =
                $feed
                    ->media()
                    ->count();

            foreach (
                $request->file(
                    'images',
                    []
                )
                as $index => $image
            ) {
                $feed
                    ->media()
                    ->create([
                        'type' =>
                            'image',

                        'file_path' =>
                            $image->store(
                                'feeds',
                                'public'
                            ),

                        'sort_order' =>
                            $existingMediaCount
                            + $index,
                    ]);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Delete Physical Files After Transaction
        |--------------------------------------------------------------------------
        */

        foreach (
            $removedMediaPaths
            as $path
        ) {
            Storage::disk('public')
                ->delete($path);
        }

        return response()->json([
            'success' => true,

            'message' =>
                'Feed post updated successfully.',

            'data' => [
                'feed' =>
                    new FeedResource(
                        $this
                            ->visibleQuery(
                                $request
                            )
                            ->findOrFail(
                                $feed->id
                            )
                    ),
            ],
        ]);
    }

    public function destroy(
        Request $request,
        Feed $feed
    ): JsonResponse {
        abort_unless(
            $request
                ->user()
                ?->can(
                    'feeds.delete'
                ),
            403
        );

        $mediaPaths =
            $feed
                ->media()
                ->pluck(
                    'file_path'
                )
                ->filter()
                ->values();

        DB::transaction(
            function () use (
                $feed
            ): void {
                /*
                |--------------------------------------------------------------------------
                | Related Feed Data
                |--------------------------------------------------------------------------
                */

                $feed
                    ->products()
                    ->detach();

                $feed
                    ->likes()
                    ->delete();

                $feed
                    ->bookmarks()
                    ->delete();

                $feed
                    ->comments()
                    ->delete();

                $feed
                    ->media()
                    ->delete();

                /*
                |--------------------------------------------------------------------------
                | Delete Feed
                |--------------------------------------------------------------------------
                */

                $feed->delete();
            }
        );

        foreach (
            $mediaPaths
            as $path
        ) {
            Storage::disk('public')
                ->delete($path);
        }

        return response()->json([
            'success' => true,

            'message' =>
                'Feed post deleted successfully.',
        ]);
    }

    public function index(
        Request $request
    ): JsonResponse {
        $feeds =
            $this
                ->visibleQuery($request)
                ->paginate(
                    min(
                        max(
                            $request->integer(
                                'per_page',
                                15
                            ),
                            1
                        ),
                        50
                    )
                );

        return response()->json([
            'success' => true,

            'data' => [
                'feeds' =>
                    FeedResource::collection(
                        $feeds
                    ),

                'meta' => [
                    'current_page' =>
                        $feeds->currentPage(),

                    'last_page' =>
                        $feeds->lastPage(),

                    'per_page' =>
                        $feeds->perPage(),

                    'total' =>
                        $feeds->total(),
                ],
            ],
        ]);
    }

    public function show(
        Request $request,
        int $feed
    ): JsonResponse {
        $model =
            $this
                ->visibleQuery($request)
                ->findOrFail(
                    $feed
                );

        return response()->json([
            'success' => true,

            'data' => [
                'feed' =>
                    new FeedResource(
                        $model
                    ),
            ],
        ]);
    }

    public function like(
        Request $request,
        int $feed
    ): JsonResponse {
        $model =
            $this
                ->visibleQuery($request)
                ->findOrFail(
                    $feed
                );

        $model
            ->likes()
            ->firstOrCreate([
                'user_id' =>
                    $request
                        ->user()
                        ->id,
            ]);

        return $this
            ->interactionResponse(
                $model,
                $request
            );
    }

    public function unlike(
        Request $request,
        int $feed
    ): JsonResponse {
        $model =
            $this
                ->visibleQuery($request)
                ->findOrFail(
                    $feed
                );

        $model
            ->likes()
            ->where(
                'user_id',
                $request
                    ->user()
                    ->id
            )
            ->delete();

        return $this
            ->interactionResponse(
                $model,
                $request
            );
    }

    public function bookmark(
        Request $request,
        int $feed
    ): JsonResponse {
        $model =
            $this
                ->visibleQuery($request)
                ->findOrFail(
                    $feed
                );

        $model
            ->bookmarks()
            ->firstOrCreate([
                'user_id' =>
                    $request
                        ->user()
                        ->id,
            ]);

        return $this
            ->interactionResponse(
                $model,
                $request
            );
    }

    public function unbookmark(
        Request $request,
        int $feed
    ): JsonResponse {
        $model =
            $this
                ->visibleQuery($request)
                ->findOrFail(
                    $feed
                );

        $model
            ->bookmarks()
            ->where(
                'user_id',
                $request
                    ->user()
                    ->id
            )
            ->delete();

        return $this
            ->interactionResponse(
                $model,
                $request
            );
    }

    public function comments(
        Request $request,
        int $feed
    ): JsonResponse {
        $model =
            $this
                ->visibleQuery($request)
                ->findOrFail(
                    $feed
                );

        $comments =
            $model
                ->comments()
                ->where(
                    'is_active',
                    true
                )
                ->with(
                    'user:id,name,display_name,avatar_path'
                )
                ->oldest()
                ->paginate(
                    min(
                        max(
                            $request->integer(
                                'per_page',
                                20
                            ),
                            1
                        ),
                        50
                    )
                );

        return response()->json([
            'success' => true,

            'data' => [
                'comments' =>
                    FeedCommentResource::collection(
                        $comments
                    ),
            ],
        ]);
    }

    public function storeComment(
        StoreFeedCommentRequest $request,
        int $feed
    ): JsonResponse {
        $model =
            $this
                ->visibleQuery($request)
                ->findOrFail(
                    $feed
                );

        $comment =
            $model
                ->comments()
                ->create([
                    'user_id' =>
                        $request
                            ->user()
                            ->id,

                    'comment' =>
                        $request
                            ->validated(
                                'comment'
                            ),
                ]);

        return response()->json([
            'success' => true,

            'data' => [
                'comment' =>
                    new FeedCommentResource(
                        $comment->load(
                            'user'
                        )
                    ),
            ],
        ], 201);
    }

    private function visibleQuery(
        Request $request
    ) {
        $userId =
            $request
                ->user()
                ?->id;

        return Feed::query()
            ->visible()
            ->with([
                'media',

                'products.primaryImage',

                'author:id,name,display_name,avatar_path',

                'likes' =>
                    fn ($query) =>
                        $userId
                            ? $query->where(
                                'user_id',
                                $userId
                            )
                            : $query
                                ->whereRaw(
                                    '1 = 0'
                                ),

                'bookmarks' =>
                    fn ($query) =>
                        $userId
                            ? $query->where(
                                'user_id',
                                $userId
                            )
                            : $query
                                ->whereRaw(
                                    '1 = 0'
                                ),
            ])
            ->withCount([
                'likes',

                'comments' =>
                    fn ($query) =>
                        $query->where(
                            'is_active',
                            true
                        ),
            ])
            ->orderByDesc(
                'published_at'
            )
            ->orderByDesc(
                'id'
            );
    }

    private function interactionResponse(
        Feed $feed,
        Request $request
    ): JsonResponse {
        return response()->json([
            'success' => true,

            'data' => [
                'feed' =>
                    new FeedResource(
                        $this
                            ->visibleQuery(
                                $request
                            )
                            ->findOrFail(
                                $feed->id
                            )
                    ),
            ],
        ]);
    }
}