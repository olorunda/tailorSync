<?php

namespace Database\Factories;

use App\Models\Design;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DesignTag>
 */
class DesignTagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tagTypes = ['fabric', 'style', 'season', 'occasion'];

        $fabricValues = ['Cotton', 'Silk', 'Linen', 'Wool', 'Polyester', 'Denim', 'Leather', 'Velvet'];
        $styleValues = ['Elegant', 'Casual', 'Formal', 'Vintage', 'Modern', 'Classic', 'Trendy', 'Bohemian'];
        $seasonValues = ['Spring', 'Summer', 'Fall', 'Winter', 'All Season'];
        $occasionValues = ['Wedding', 'Party', 'Business', 'Casual', 'Formal', 'Everyday'];

        $tagType = $this->faker->randomElement($tagTypes);

        // Select appropriate tag value based on tag type
        $tagValue = match ($tagType) {
            'fabric' => $this->faker->randomElement($fabricValues),
            'style' => $this->faker->randomElement($styleValues),
            'season' => $this->faker->randomElement($seasonValues),
            'occasion' => $this->faker->randomElement($occasionValues),
            default => $this->faker->word(),
        };

        return [
            'design_id' => Design::factory(),
            'tag_type' => $tagType,
            'tag_value' => $tagValue,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Configure the factory to create a fabric tag.
     */
    public function fabric()
    {
        return $this->state(function (array $attributes) {
            $fabricValues = ['Cotton', 'Silk', 'Linen', 'Wool', 'Polyester', 'Denim', 'Leather', 'Velvet'];
            return [
                'tag_type' => 'fabric',
                'tag_value' => $this->faker->randomElement($fabricValues),
            ];
        });
    }

    /**
     * Configure the factory to create a style tag.
     */
    public function style()
    {
        return $this->state(function (array $attributes) {
            $styleValues = ['Elegant', 'Casual', 'Formal', 'Vintage', 'Modern', 'Classic', 'Trendy', 'Bohemian'];
            return [
                'tag_type' => 'style',
                'tag_value' => $this->faker->randomElement($styleValues),
            ];
        });
    }
}
