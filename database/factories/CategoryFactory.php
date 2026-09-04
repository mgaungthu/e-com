<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(
            2,
            true,
        );

        return [
            'parent_id' => null,

            'name' => Str::title($name),

            'slug' => Str::slug($name)
                . '-'
                . fake()->unique()->numberBetween(
                    1000,
                    999999,
                ),

            'description' => fake()
                ->optional()
                ->sentence(),

            'is_active' => true,

            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(
            fn (array $attributes) => [
                'is_active' => false,
            ],
        );
    }

    public function childOf(
        Category $category,
    ): static {
        return $this->state(
            fn (array $attributes) => [
                'parent_id' =>
                    $category->id,
            ],
        );
    }
}