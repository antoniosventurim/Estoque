<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'barcode' => fake()->unique()->numerify('#############'),
            'category_id' => null,
            'stock' => 10,
            'min_stock' => 0,
            'max_stock' => 0,
            'unit' => 'un',
            'is_active' => true,
            'note' => null,
            'image' => null,
        ];
    }
}
