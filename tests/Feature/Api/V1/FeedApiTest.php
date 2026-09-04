<?php

namespace Tests\Feature\Api\V1;

use App\Enums\FeedStatus;
use App\Models\Feed;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FeedApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_visible_feeds_are_returned_with_ordered_media_and_products(): void
    {
        $visible = $this->feed();
        $visible->media()->createMany([
            ['type' => 'image', 'file_path' => 'feeds/second.jpg', 'sort_order' => 1],
            ['type' => 'image', 'file_path' => 'feeds/first.jpg', 'sort_order' => 0],
        ]);
        $firstProduct = $this->product('first');
        $secondProduct = $this->product('second');
        $visible->products()->attach([$secondProduct->id => ['sort_order' => 1], $firstProduct->id => ['sort_order' => 0]]);
        $this->feed(['status' => FeedStatus::Draft]);
        $this->feed(['status' => FeedStatus::Archived]);
        $this->feed(['is_active' => false]);
        $this->feed(['published_at' => now()->addHour()]);

        $this->getJson('/api/v1/feeds')
            ->assertOk()
            ->assertJsonCount(1, 'data.feeds')
            ->assertJsonPath('data.feeds.0.id', $visible->id)
            ->assertJsonPath('data.feeds.0.media.0.file_path', null)
            ->assertJsonPath('data.feeds.0.media.0.id', $visible->media()->orderBy('sort_order')->first()->id)
            ->assertJsonPath('data.feeds.0.products.0.id', $firstProduct->id)
            ->assertJsonPath('data.feeds.0.viewer.is_liked', false);
    }

    public function test_like_bookmark_and_comment_are_authenticated_idempotent_interactions(): void
    {
        $feed = $this->feed();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/feeds/{$feed->id}/like")->assertOk()->assertJsonPath('data.feed.viewer.is_liked', true);
        $this->postJson("/api/v1/feeds/{$feed->id}/like")->assertOk();
        $this->assertDatabaseCount('feed_likes', 1);
        $this->postJson("/api/v1/feeds/{$feed->id}/bookmark")->assertOk()->assertJsonPath('data.feed.viewer.is_bookmarked', true);
        $this->assertDatabaseCount('feed_bookmarks', 1);
        $this->postJson("/api/v1/feeds/{$feed->id}/comments", ['comment' => 'Looks great!'])->assertCreated();
        $this->getJson("/api/v1/feeds/{$feed->id}")->assertJsonPath('data.feed.stats.likes', 1)->assertJsonPath('data.feed.stats.comments', 1);
        $this->deleteJson("/api/v1/feeds/{$feed->id}/like")->assertOk();
        $this->deleteJson("/api/v1/feeds/{$feed->id}/bookmark")->assertOk();
        $this->assertDatabaseCount('feed_likes', 0);
        $this->assertDatabaseCount('feed_bookmarks', 0);
    }

    public function test_guests_cannot_interact_with_or_comment_on_feeds(): void
    {
        $feed = $this->feed();
        $this->postJson("/api/v1/feeds/{$feed->id}/like")->assertUnauthorized();
        $this->postJson("/api/v1/feeds/{$feed->id}/bookmark")->assertUnauthorized();
        $this->postJson("/api/v1/feeds/{$feed->id}/comments", ['comment' => 'Hi'])->assertUnauthorized();
    }

    public function test_feed_admin_create_accepts_images_and_rejects_video_uploads(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');
        Storage::fake('public');

        $this->actingAs($admin)->post('/admin/feeds', [
            'caption' => 'New post', 'status' => 'published', 'is_active' => true,
            'published_at' => now()->toDateTimeString(),
            'images' => [UploadedFile::fake()->image('feed.png')],
        ])->assertCreated()->assertJsonPath('data.feed.media.0.type', 'image');

        $this->actingAs($admin)->post('/admin/feeds', [
            'status' => 'published', 'is_active' => true,
            'images' => [UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4')],
        ])->assertUnprocessable()->assertJsonValidationErrors('images.0');
    }

    public function test_only_authorized_staff_can_publish_a_feed_from_mobile(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Storage::fake('public');

        $customer = User::factory()->create([
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $customer->assignRole('customer');
        Sanctum::actingAs($customer);

        $this->post('/api/v1/feeds', [
            'status' => 'published',
            'is_active' => true,
            'published_at' => now()->toDateTimeString(),
            'images' => [UploadedFile::fake()->image('feed.png')],
        ])->assertForbidden();

        $admin = User::factory()->create([
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        $this->post('/api/v1/feeds', [
            'caption' => 'Published from mobile',
            'status' => 'published',
            'is_active' => true,
            'published_at' => now()->toDateTimeString(),
            'images' => [UploadedFile::fake()->image('feed.png')],
        ])->assertCreated()
            ->assertJsonPath('data.feed.caption', 'Published from mobile');

        $this->assertDatabaseHas('feeds', [
            'user_id' => $admin->id,
            'caption' => 'Published from mobile',
        ]);
    }

    private function feed(array $overrides = []): Feed
    {
        return Feed::query()->create(['caption' => 'A feed post', 'status' => FeedStatus::Published, 'is_active' => true, 'published_at' => now()->subMinute(), ...$overrides]);
    }

    private function product(string $suffix): Product
    {
        return Product::query()->create(['name' => "Product {$suffix}", 'slug' => "product-{$suffix}", 'sku' => "SKU-{$suffix}", 'price' => 10000, 'stock_quantity' => 10, 'is_active' => true]);
    }
}
