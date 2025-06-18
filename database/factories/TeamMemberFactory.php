<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamMember>
 */
class TeamMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles = ['cutter', 'tailor', 'designer', 'delivery', 'admin', 'other'];

        $skillsByRole = [
            'cutter' => ['Pattern Making', 'Fabric Cutting', 'Measurement Taking', 'Material Selection'],
            'tailor' => ['Hand Sewing', 'Machine Sewing', 'Alterations', 'Embroidery', 'Beading'],
            'designer' => ['Sketching', 'CAD Design', 'Color Theory', 'Trend Analysis', 'Draping'],
            'delivery' => ['Logistics', 'Customer Service', 'Route Planning', 'Packaging'],
            'admin' => ['Scheduling', 'Inventory Management', 'Customer Relations', 'Accounting'],
            'other' => ['Photography', 'Marketing', 'Social Media', 'Quality Control'],
        ];

        $role = $this->faker->randomElement($roles);

        // Select 2-4 skills based on the role
        $availableSkills = $skillsByRole[$role] ?? $skillsByRole['other'];
        $skills = $this->faker->randomElements(
            $availableSkills,
            $this->faker->numberBetween(2, min(4, count($availableSkills)))
        );

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'role' => $role,
            'skills' => $skills,
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Configure the factory to create an active team member.
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    /**
     * Configure the factory to create a tailor.
     */
    public function tailor()
    {
        return $this->state(function (array $attributes) {
            $tailorSkills = ['Hand Sewing', 'Machine Sewing', 'Alterations', 'Embroidery', 'Beading'];
            return [
                'role' => 'tailor',
                'skills' => $this->faker->randomElements(
                    $tailorSkills,
                    $this->faker->numberBetween(2, 4)
                ),
            ];
        });
    }

    /**
     * Configure the factory to create a designer.
     */
    public function designer()
    {
        return $this->state(function (array $attributes) {
            $designerSkills = ['Sketching', 'CAD Design', 'Color Theory', 'Trend Analysis', 'Draping'];
            return [
                'role' => 'designer',
                'skills' => $this->faker->randomElements(
                    $designerSkills,
                    $this->faker->numberBetween(2, 4)
                ),
            ];
        });
    }
}
