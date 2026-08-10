<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FeedStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Feed\StoreFeedRequest;
use App\Http\Requests\Admin\Feed\UpdateFeedRequest;
use App\Models\Feed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('feeds.view');
        $request->validate(['search' => ['nullable', 'string', 'max:255'], 'status' => ['nullable', Rule::enum(FeedStatus::class)], 'is_active' => ['nullable', 'boolean'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $feeds = Feed::query()->with(['media', 'products.primaryImage', 'author:id,name,display_name,avatar_path'])->withCount(['likes', 'comments' => fn ($query) => $query->where('is_active', true)])
            ->when($request->filled('search'), fn ($query) => $query->where('caption', 'like', '%'.trim((string) $request->input('search')).'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->has('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderByDesc('published_at')->latest('id')->paginate($request->integer('per_page', 15));

        return response()->json(['success' => true, 'data' => $feeds]);
    }

    public function show(Feed $feed): JsonResponse
    {
        Gate::authorize('feeds.view');

        return response()->json(['success' => true, 'data' => ['feed' => $this->loadFeed($feed)]]);
    }

    public function store(StoreFeedRequest $request): JsonResponse
    {
        $data = $request->validated();
        $feed = DB::transaction(function () use ($request, $data): Feed {
            $feed = Feed::query()->create(['user_id' => $request->user()->id, 'caption' => $data['caption'] ?? null, 'status' => $data['status'], 'is_active' => $request->boolean('is_active'), 'published_at' => $data['published_at'] ?? null]);
            $this->syncProducts($feed, $data['products'] ?? []);
            $this->syncMedia($feed, $request);

            return $feed;
        });

        return response()->json(['success' => true, 'message' => 'Feed post created successfully.', 'data' => ['feed' => $this->loadFeed($feed)]], 201);
    }

    public function update(UpdateFeedRequest $request, Feed $feed): JsonResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($request, $feed, $data): void {
            $feed->update(['caption' => $data['caption'] ?? null, 'status' => $data['status'], 'is_active' => $request->boolean('is_active'), 'published_at' => $data['published_at'] ?? null]);
            $this->syncProducts($feed, $data['products'] ?? []);
            $this->syncMedia($feed, $request);
        });

        return response()->json(['success' => true, 'message' => 'Feed post updated successfully.', 'data' => ['feed' => $this->loadFeed($feed)]]);
    }

    public function destroy(Feed $feed): JsonResponse
    {
        Gate::authorize('feeds.delete');
        $paths = $feed->media()->pluck('file_path');
        DB::transaction(fn () => $feed->delete());
        Storage::disk('public')->delete($paths->all());

        return response()->json(['success' => true, 'message' => 'Feed post deleted successfully.']);
    }

    private function syncProducts(Feed $feed, array $products): void
    {
        $sync = [];
        foreach ($products as $index => $product) {
            $sync[$product['product_id']] = ['sort_order' => $product['sort_order'] ?? $index];
        }
        $feed->products()->sync($sync);
    }

    private function syncMedia(Feed $feed, Request $request): void
    {
        $removeIds = collect($request->input('remove_media_ids', []))->map(fn ($id) => (int) $id);
        $removed = $feed->media()->whereIn('id', $removeIds)->get();
        foreach ($removed as $media) {
            Storage::disk('public')->delete($media->file_path);
            $media->delete();
        }
        $remaining = $feed->media()->get()->keyBy('id');
        $order = collect($request->input('media_order', []))->map(fn ($id) => (int) $id)->filter(fn ($id) => $remaining->has($id));
        $ordered = $order->map(fn ($id) => $remaining->get($id))->concat($remaining->except($order->all())->values());
        foreach ($ordered as $index => $media) {
            $media->update(['sort_order' => $index]);
        }
        $sortOrder = $ordered->count();
        foreach ($request->file('images', []) as $image) {
            $feed->media()->create(['type' => 'image', 'file_path' => $image->store('feeds', 'public'), 'sort_order' => $sortOrder++]);
        }
    }

    private function loadFeed(Feed $feed): Feed
    {
        return $feed->load(['media', 'products.primaryImage', 'author:id,name,display_name,avatar_path'])->loadCount(['likes', 'comments' => fn ($query) => $query->where('is_active', true)]);
    }
}
