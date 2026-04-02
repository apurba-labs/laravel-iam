<?php
namespace ApurbaLabs\IAM\Database\Factories;

use ApurbaLabs\IAM\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
/**
 * Package owns the model.
 * This factory generates realistic roles for testing.
 * It uses unique job titles as role names and creates corresponding slugs.
 * The description is a random sentence to provide context about the role.
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition()
    {
        $name = $this->faker->unique()->jobTitle; // e.g., "Inventory Manager"

        return [
            'name' => $name,
            'slug' => Str::slug($name), // Matches the name
            'description' => $this->faker->sentence(), // Random description
        ];
    }

    /**
     * Set a dynamic module for the stage.
     */
    public function forName(string $name)
    {
        return $this->state(fn () => [
            'name' => $name,
            'slug' => Str::slug($name),
        ]);
    }
}