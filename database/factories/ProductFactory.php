<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),

            'name' => Str::title($name),

            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(
                1000,
                999999,
            ),

            'sku' => fake()->unique()->bothify(
                'BSC-####-????',
            ),

            'barcode' => null,

            'price' => fake()->numberBetween(
                10000,
                150000,
            ),

            'sale_price' => null,

            'stock_quantity' => 10,

            'low_stock_threshold' => 5,

            'short_description' => fake()->sentence(),

            'description' => fake()->paragraph(),

            'is_active' => true,

            'is_featured' => false,
        ];
    }

    /**
     * Mark the product as inactive.
     */
    public function inactive(): static
    {
        return $this->state(
            fn (array $attributes) => [
                'is_active' => false,
            ],
        );
    }

    /**
     * Mark the product as featured.
     */
    public function featured(): static
    {
        return $this->state(
            fn (array $attributes) => [
                'is_featured' => true,
            ],
        );
    }

    /**
     * Mark the product as out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(
            fn (array $attributes) => [
                'stock_quantity' => 0,
            ],
        );
    }

    /**
     * Mark the product as low stock.
     */
    public function lowStock(): static
    {
        return $this->state(
            fn (array $attributes) => [
                'stock_quantity' => 2,
                'low_stock_threshold' => 5,
            ],
        );
    }

    /**
     * Add a sale price.
     */
    public function onSale(): static
    {
        return $this->state(
            function (array $attributes) {
                $price = (float) (
                    $attributes['price'] ??
                    50000
                );

                return [
                    'sale_price' => round(
                        $price * 0.8,
                        2,
                    ),
                ];
            },
        );
    }
}