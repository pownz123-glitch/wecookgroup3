<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_number' => fake()->unique()->bothify('AST-######'),
            'asset_type' => fake()->randomElement([
                'Transformer',
                'Electric meter',
                'Distribution equipment',
                'Cable',
                'Maintenance equipment',
                'Other',
            ]),
            'name' => fake()->words(3, true),
            'serial_number' => fake()->optional()->bothify('SN-########'),
            'status' => 'active',
            'condition' => 'good',
            'installation_date' => fake()->optional()->date(),
            'description' => fake()->sentence(),
        ];
    }
}
