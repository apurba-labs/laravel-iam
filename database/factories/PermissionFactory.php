<?php
namespace ApurbaLabs\IAM\Database\Factories;

use ApurbaLabs\IAM\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * Package owns the model.
 * This factory generates realistic permissions for testing.
 * It creates permissions in the format of "resource.action" (e.g., "user.create", "invoice.view").
 * It also includes a helper method for generating wildcard permissions (e.g., "user.*").
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition()
    {
        // Generates realistic permissions like "user.create" or "invoice.view"
        $resource = $this->faker->word;
        $actions = ['create', 'view', 'edit', 'delete', 'manage', '*'];
        $action = $this->faker->randomElement($actions);

        return [
            'name' => "{$resource}.{$action}",
            'description' => "Allows user to {$action} {$resource}",
        ];
    }

    /**
     * Helper for Wildcards
     */
    public function wildcard(string $resource)
    {
        return $this->state(fn () => [
            'name' => "{$resource}.*",
        ]);
    }
}