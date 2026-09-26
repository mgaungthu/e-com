<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavouriteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_favourites(): void
    {
        $this
            ->getJson(
                $this->apiUrl('/v1/favourites'),
            )
            ->assertUnauthorized();
    }

    public function test_authenticated_customer_can_add_product_to_favourites(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);

        $product = Product::factory()->create([
            'is_active' => true,
        ]);

        $token = $user
            ->createToken('Test Device')
            ->plainTextToken;

        $response = $this
            ->withToken($token)
            ->postJson(
                $this->apiUrl(
                    '/v1/favourites/'.$product->id,
                ),
            );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'data.product.id',
                $product->id,
            )
            ->assertJsonPath(
                'data.product.is_favourite',
                true,
            );

        $this->assertDatabaseHas('product_favourites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_adding_same_product_twice_does_not_create_duplicate_favourite(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);

        $product = Product::factory()->create([
            'is_active' => true,
        ]);

        $token = $user
            ->createToken('Test Device')
            ->plainTextToken;

        $this
            ->withToken($token)
            ->postJson(
                $this->apiUrl(
                    '/v1/favourites/'.$product->id,
                ),
            )
            ->assertOk();

        $this
            ->withToken($token)
            ->postJson(
                $this->apiUrl(
                    '/v1/favourites/'.$product->id,
                ),
            )
            ->assertOk();

        $this->assertDatabaseCount(
            'product_favourites',
            1,
        );
    }

    public function test_authenticated_customer_can_get_their_favourites(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);

        $otherUser = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);

        $product = Product::factory()->create([
            'is_active' => true,
        ]);

        $otherProduct = Product::factory()->create([
            'is_active' => true,
        ]);

        $user
            ->favouriteProducts()
            ->attach($product->id);

        $otherUser
            ->favouriteProducts()
            ->attach($otherProduct->id);

        $token = $user
            ->createToken('Test Device')
            ->plainTextToken;

        $response = $this
            ->withToken($token)
            ->getJson(
                $this->apiUrl('/v1/favourites'),
            );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'data.products.0.id',
                $product->id,
            )
            ->assertJsonPath(
                'data.products.0.is_favourite',
                true,
            )
            ->assertJsonMissing([
                'id' => $otherProduct->id,
            ]);
    }

    public function test_inactive_products_are_not_returned_in_favourites(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);

        $activeProduct = Product::factory()->create([
            'is_active' => true,
        ]);

        $inactiveProduct = Product::factory()->create([
            'is_active' => false,
        ]);

        $user
            ->favouriteProducts()
            ->attach([
                $activeProduct->id,
                $inactiveProduct->id,
            ]);

        $token = $user
            ->createToken('Test Device')
            ->plainTextToken;

        $response = $this
            ->withToken($token)
            ->getJson(
                $this->apiUrl('/v1/favourites'),
            );

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $activeProduct->id,
            ])
            ->assertJsonMissing([
                'id' => $inactiveProduct->id,
            ]);
    }

    public function test_authenticated_customer_can_remove_product_from_favourites(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);

        $product = Product::factory()->create([
            'is_active' => true,
        ]);

        $user
            ->favouriteProducts()
            ->attach($product->id);

        $token = $user
            ->createToken('Test Device')
            ->plainTextToken;

        $response = $this
            ->withToken($token)
            ->deleteJson(
                $this->apiUrl(
                    '/v1/favourites/'.$product->id,
                ),
            );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'data.product.id',
                $product->id,
            )
            ->assertJsonPath(
                'data.product.is_favourite',
                false,
            );

        $this->assertDatabaseMissing('product_favourites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_guest_product_detail_returns_is_favourite_false(): void
    {
        $product = Product::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->getJson(
            $this->apiUrl(
                '/v1/products/'.$product->slug,
            ),
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.product.is_favourite',
                false,
            );
    }

    public function test_authenticated_product_detail_returns_correct_favourite_state(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);

        $favouriteProduct = Product::factory()->create([
            'is_active' => true,
        ]);

        $normalProduct = Product::factory()->create([
            'is_active' => true,
        ]);

        $user
            ->favouriteProducts()
            ->attach($favouriteProduct->id);

        $token = $user
            ->createToken('Test Device')
            ->plainTextToken;

        $this
            ->withToken($token)
            ->getJson(
                $this->apiUrl(
                    '/v1/products/'.$favouriteProduct->slug,
                ),
            )
            ->assertOk()
            ->assertJsonPath(
                'data.product.is_favourite',
                true,
            );

        $this
            ->withToken($token)
            ->getJson(
                $this->apiUrl(
                    '/v1/products/'.$normalProduct->slug,
                ),
            )
            ->assertOk()
            ->assertJsonPath(
                'data.product.is_favourite',
                false,
            );
    }

    private function apiUrl(string $path): string
    {
        return 'http://'
            .config('app.api_domain')
            .$path;
    }
}