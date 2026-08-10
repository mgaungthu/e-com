<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_active_categories(): void
    {
        $activeCategory = Category::factory()->create([
            'name' => 'Active Category',
            'slug' => 'active-category',
            'is_active' => true,
        ]);

        Category::factory()->create([
            'name' => 'Inactive Category',
            'slug' => 'inactive-category',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/categories');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.categories.0.id', $activeCategory->id)
            ->assertJsonMissing([
                'slug' => 'inactive-category',
            ]);
    }

    public function test_it_returns_a_category_by_slug(): void
    {
        $category = Category::factory()->create([
            'name' => 'Perfume',
            'slug' => 'perfume',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/categories/'.$category->slug);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.category.slug', 'perfume');
    }

    public function test_inactive_category_is_not_publicly_available(): void
    {
        $category = Category::factory()->create([
            'slug' => 'hidden-category',
            'is_active' => false,
        ]);

        $this
            ->getJson('/api/v1/categories/'.$category->slug)
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_it_returns_only_active_products(): void
    {
        $category = Category::factory()->create([
            'is_active' => true,
        ]);

        $activeProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Active Product',
            'slug' => 'active-product',
            'is_active' => true,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Hidden Product',
            'slug' => 'hidden-product',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/products');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.products.0.id', $activeProduct->id)
            ->assertJsonMissing([
                'slug' => 'hidden-product',
            ]);
    }

    public function test_it_filters_products_by_category_slug(): void
    {
        $perfume = Category::factory()->create([
            'name' => 'Perfume',
            'slug' => 'perfume',
            'is_active' => true,
        ]);

        $fashion = Category::factory()->create([
            'name' => 'Fashion',
            'slug' => 'fashion',
            'is_active' => true,
        ]);

        $perfumeProduct = Product::factory()->create([
            'category_id' => $perfume->id,
            'slug' => 'perfume-product',
            'is_active' => true,
        ]);

        Product::factory()->create([
            'category_id' => $fashion->id,
            'slug' => 'fashion-product',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/products?category=perfume');

        $response
            ->assertOk()
            ->assertJsonPath('data.products.0.id', $perfumeProduct->id)
            ->assertJsonMissing([
                'slug' => 'fashion-product',
            ]);
    }

    public function test_it_returns_featured_products(): void
    {
        $category = Category::factory()->create([
            'is_active' => true,
        ]);

        $featuredProduct = Product::factory()->create([
            'category_id' => $category->id,
            'slug' => 'featured-product',
            'is_active' => true,
            'is_featured' => true,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'slug' => 'normal-product',
            'is_active' => true,
            'is_featured' => false,
        ]);

        $response = $this->getJson('/api/v1/products/featured');

        $response
            ->assertOk()
            ->assertJsonPath('data.products.0.id', $featuredProduct->id)
            ->assertJsonMissing([
                'slug' => 'normal-product',
            ]);
    }

    public function test_it_returns_product_by_slug(): void
    {
        $category = Category::factory()->create([
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/products/'.$product->slug);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.product.slug', 'test-product');
    }

    public function test_inactive_product_is_not_publicly_available(): void
    {
        $category = Category::factory()->create([
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'slug' => 'hidden-product',
            'is_active' => false,
        ]);

        $this
            ->getJson('/api/v1/products/'.$product->slug)
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }
}
