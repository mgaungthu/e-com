<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Feed\StoreFeedCommentRequest;
use App\Http\Resources\Api\V1\FeedCommentResource;
use App\Http\Resources\Api\V1\FeedResource;
use App\Models\Feed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $feeds = $this->visibleQuery($request)->paginate(min(max($request->integer('per_page', 15), 1), 50));

        return response()->json(['success' => true, 'data' => ['feeds' => FeedResource::collection($feeds), 'meta' => ['current_page' => $feeds->currentPage(), 'last_page' => $feeds->lastPage(), 'per_page' => $feeds->perPage(), 'total' => $feeds->total()]]]);
    }

    public function show(Request $request, int $feed): JsonResponse
    {
        $model = $this->visibleQuery($request)->findOrFail($feed);

        return response()->json(['success' => true, 'data' => ['feed' => new FeedResource($model)]]);
    }

    public function like(Request $request, int $feed): JsonResponse
    {
        $model = $this->visibleQuery($request)->findOrFail($feed);
        $model->likes()->firstOrCreate(['user_id' => $request->user()->id]);

        return $this->interactionResponse($model, $request);
    }

    public function unlike(Request $request, int $feed): JsonResponse
    {
        $model = $this->visibleQuery($request)->findOrFail($feed);
        $model->likes()->where('user_id', $request->user()->id)->delete();

        return $this->interactionResponse($model, $request);
    }

    public function bookmark(Request $request, int $feed): JsonResponse
    {
        $model = $this->visibleQuery($request)->findOrFail($feed);
        $model->bookmarks()->firstOrCreate(['user_id' => $request->user()->id]);

        return $this->interactionResponse($model, $request);
    }

    public function unbookmark(Request $request, int $feed): JsonResponse
    {
        $model = $this->visibleQuery($request)->findOrFail($feed);
        $model->bookmarks()->where('user_id', $request->user()->id)->delete();

        return $this->interactionResponse($model, $request);
    }

    public function comments(Request $request, int $feed): JsonResponse
    {
        $model = $this->visibleQuery($request)->findOrFail($feed);
        $comments = $model->comments()->where('is_active', true)->with('user:id,name,display_name,avatar_path')->oldest()->paginate(min(max($request->integer('per_page', 20), 1), 50));

        return response()->json(['success' => true, 'data' => ['comments' => FeedCommentResource::collection($comments)]]);
    }

    public function storeComment(StoreFeedCommentRequest $request, int $feed): JsonResponse
    {
        $model = $this->visibleQuery($request)->findOrFail($feed);
        $comment = $model->comments()->create(['user_id' => $request->user()->id, 'comment' => $request->validated('comment')]);

        return response()->json(['success' => true, 'data' => ['comment' => new FeedCommentResource($comment->load('user'))]], 201);
    }

    private function visibleQuery(Request $request)
    {
        $userId = $request->user()?->id;

        return Feed::query()->visible()->with(['media', 'products.primaryImage', 'author:id,name,display_name,avatar_path', 'likes' => fn ($query) => $userId ? $query->where('user_id', $userId) : $query->whereRaw('1 = 0'), 'bookmarks' => fn ($query) => $userId ? $query->where('user_id', $userId) : $query->whereRaw('1 = 0')])->withCount(['likes', 'comments' => fn ($query) => $query->where('is_active', true)])->orderByDesc('published_at')->orderByDesc('id');
    }

    private function interactionResponse(Feed $feed, Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => ['feed' => new FeedResource($this->visibleQuery($request)->findOrFail($feed->id))]]);
    }
}
