<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Design>
 */
class DesignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Dress', 'Shirt', 'Pants', 'Skirt', 'Suit', 'Jacket', 'Traditional', 'Casual', 'Formal'];
        $collections = ['Summer', 'Winter', 'Spring', 'Fall', 'Wedding', 'Party', 'Casual', 'Business'];
        $materials = ['Cotton', 'Silk', 'Linen', 'Wool', 'Polyester', 'Denim', 'Leather', 'Velvet'];
        $tags = ['Elegant', 'Casual', 'Formal', 'Vintage', 'Modern', 'Classic', 'Trendy', 'Bohemian'];

        // Generate 1-3 random materials
        $selectedMaterials = $this->faker->randomElements($materials, $this->faker->numberBetween(1, 3));

        // Generate 1-4 random tags
        $selectedTags = $this->faker->randomElements($tags, $this->faker->numberBetween(1, 4));

        // Generate 1-3 random image paths
        $images = [];
        $imageCount = $this->faker->numberBetween(1, 3);
        for ($i = 0; $i < $imageCount; $i++) {
            $images[] = 'designs/' . $this->faker->uuid . '.jpg';
        }

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true) . ' Design',
            'category' => $this->faker->randomElement($categories),
            'description' => $this->faker->paragraph(),
            'materials' => $selectedMaterials,
            'tags' => $selectedTags,
            'images' => $images,
            'primary_image' => $images[0] ?? null,
            'old_image_path' => null,
            'collection' => $this->faker->randomElement($collections),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }
}
