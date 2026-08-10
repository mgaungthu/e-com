<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('public');
    }

    public function test_product_manager_can_create_a_product_with_an_ordered_gallery(): void
    {
        $user = $this->userWithRole('product_manager');
        $firstImage = UploadedFile::fake()->image('front.jpg');
        $secondImage = UploadedFile::fake()->image('back.jpg');

        $response = $this
            ->actingAs($user)
            ->post('/admin/products', [
                ...$this->productPayload(),
                'images' => [$firstImage, $secondImage],
                'primary_new_image_index' => 1,
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.product.images.0.sort_order', 0)
            ->assertJsonPath('data.product.images.1.is_primary', true);

        $product = Product::query()->firstOrFail();
        $images = $product->images()->get();

        $this->assertCount(2, $images);
        $this->assertSame($images[1]->path, $product->image_path);
        Storage::disk('public')->assertExists($images[0]->path);
        Storage::disk('public')->assertExists($images[1]->path);
    }

    public function test_product_manager_can_update_gallery_order_primary_image_and_removals(): void
    {
        $user = $this->userWithRole('product_manager');
        $product = Product::query()->create($this->productAttributes());
        $first = $product->images()->create([
            'path' => UploadedFile::fake()->image('first.jpg')->store('products', 'public'),
            'alt_text' => $product->name,
            'is_primary' => true,
            'sort_order' => 0,
        ]);
        $second = $product->images()->create([
            'path' => UploadedFile::fake()->image('second.jpg')->store('products', 'public'),
            'alt_text' => $product->name,
            'is_primary' => false,
            'sort_order' => 1,
        ]);
        $product->update(['image_path' => $first->path]);

        $response = $this
            ->actingAs($user)
            ->post("/admin/products/{$product->id}", [
                '_method' => 'PUT',
                ...$this->productPayload(['name' => 'Updated product']),
                'images' => [UploadedFile::fake()->image('new.jpg')],
                'removed_image_ids' => [$first->id],
                'image_order' => [$second->id],
                'primary_new_image_index' => 0,
            ]);

        $response->assertOk()->assertJsonPath('data.product.name', 'Updated product');

        $product->refresh();
        $images = $product->images()->get();

        $this->assertCount(2, $images);
        $this->assertFalse($images->contains('id', $first->id));
        $this->assertSame($second->id, $images[0]->id);
        $this->assertTrue($images[1]->is_primary);
        $this->assertSame($images[1]->path, $product->image_path);
        Storage::disk('public')->assertMissing($first->path);
    }

    public function test_product_routes_enforce_permissions(): void
    {
        $user = $this->userWithRole('order_manager');
        $product = Product::query()->create($this->productAttributes());

        $this->actingAs($user)->getJson('/admin/products')->assertForbidden();
        $this->actingAs($user)->getJson("/admin/products/{$product->id}")->assertForbidden();
        $this->actingAs($user)->post('/admin/products', $this->productPayload())->assertForbidden();
        $this->actingAs($user)->deleteJson("/admin/products/{$product->id}")->assertForbidden();
    }

    public function test_gallery_rejects_images_owned_by_another_product(): void
    {
        $user = $this->userWithRole('product_manager');
        $product = Product::query()->create($this->productAttributes());
        $otherProduct = Product::query()->create($this->productAttributes([
            'name' => 'Other product',
            'slug' => 'other-product',
            'sku' => 'OTHER-001',
        ]));
        $foreignImage = $otherProduct->images()->create([
            'path' => 'products/foreign.jpg',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $this
            ->actingAs($user)
            ->putJson("/admin/products/{$product->id}", [
                ...$this->productPayload(),
                'removed_image_ids' => [$foreignImage->id],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('removed_image_ids');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole($role);

        return $user;
    }

    private function productPayload(array $overrides = []): array
    {
        return [
            'category_id' => null,
            'name' => 'Test product',
            'slug' => 'test-product',
            'sku' => 'TEST-001',
            'barcode' => null,
            'short_description' => null,
            'description' => null,
            'price' => 25000,
            'sale_price' => 20000,
            'stock_quantity' => 10,
            'low_stock_threshold' => 3,
            'is_active' => true,
            'is_featured' => false,
            'seo_title' => null,
            'seo_description' => null,
            ...$overrides,
        ];
    }

    private function productAttributes(array $overrides = []): array
    {
        return [
            ...$this->productPayload(),
            'image_path' => null,
            ...$overrides,
        ];
    }
}
