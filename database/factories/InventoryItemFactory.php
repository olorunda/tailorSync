<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['Fabric', 'Button', 'Zipper', 'Thread', 'Lining', 'Interfacing', 'Trim', 'Elastic', 'Tool'];
        $units = ['Meter', 'Yard', 'Piece', 'Roll', 'Spool', 'Pack', 'Box'];
        $locations = ['Main Storage', 'Workshop', 'Display Room', 'Shelf A', 'Shelf B', 'Drawer 1', 'Drawer 2', 'Cabinet 3'];

        $type = $this->faker->randomElement($types);
        $unit = $this->faker->randomElement($units);

        // Generate a random SKU
        $sku = strtoupper(substr($type, 0, 2)) . '-' . $this->faker->unique()->numberBetween(1000, 9999);

        // Generate random quantity and price based on type
        $quantity = $this->faker->randomFloat(2, 1, 100);
        $unitPrice = match ($type) {
            'Fabric' => $this->faker->randomFloat(2, 5, 50),
            'Button' => $this->faker->randomFloat(2, 0.5, 5),
            'Zipper' => $this->faker->randomFloat(2, 1, 10),
            'Thread' => $this->faker->randomFloat(2, 2, 15),
            'Tool' => $this->faker->randomFloat(2, 10, 200),
            default => $this->faker->randomFloat(2, 1, 20),
        };

        $totalCost = $quantity * $unitPrice;

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true) . ' ' . $type,
            'sku' => $sku,
            'type' => $type,
            'description' => $this->faker->paragraph(),
            'image_path' => $this->faker->optional(0.7)->imageUrl(640, 480, 'fashion'),
            'image' => null,
            'quantity' => $quantity,
            'unit' => $unit,
            'unit_price' => $unitPrice,
            'total_cost' => $totalCost,
            'reorder_level' => $this->faker->randomFloat(2, 5, 20),
            'supplier' => $this->faker->company(),
            'supplier_contact' => $this->faker->phoneNumber(),
            'location' => $this->faker->randomElement($locations),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Configure the factory to create a fabric inventory item.
     */
    public function fabric()
    {
        return $this->state(function (array $attributes) {
            $quantity = $this->faker->randomFloat(2, 5, 100);
            $unitPrice = $this->faker->randomFloat(2, 5, 50);

            return [
                'type' => 'Fabric',
                'name' => $this->faker->words(2, true) . ' Fabric',
                'sku' => 'FA-' . $this->faker->unique()->numberBetween(1000, 9999),
                'unit' => $this->faker->randomElement(['Meter', 'Yard']),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_cost' => $quantity * $unitPrice,
            ];
        });
    }

    /**
     * Configure the factory to create a low stock inventory item.
     */
    public function lowStock()
    {
        return $this->state(function (array $attributes) {
            $reorderLevel = $this->faker->randomFloat(2, 10, 20);
            $quantity = $this->faker->randomFloat(2, 1, $reorderLevel * 0.9);

            return [
                'quantity' => $quantity,
                'reorder_level' => $reorderLevel,
            ];
        });
    }
}
