<?php

namespace ApurbaLabs\IAM\Database\Factories;

use ApurbaLabs\IAM\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition()
    {
        $resource = $this->faker->word;
        $actions = ['create', 'view', 'edit', 'delete', 'manage', '*'];
        $action = $this->faker->randomElement($actions);
        
        // The unique identifier used for logic
        $slug = "{$resource}.{$action}";

        return [
            'slug' => $slug,
            'name' => Str::headline(str_replace('.', ' ', $slug)),
            'resource' => $resource,
            'action' => $action,
            'description' => "Allows user to {$action} {$resource} resources.",
        ];
    }

    /**
     * Helper for Resource Wildcards (e.g., 'invoice.*')
     */
    public function wildcard(string $resource)
    {
        return $this->state(fn () => [
            'slug' => "{$resource}.*",
            'name' => Str::headline($resource) . ' Full Access',
            'resource' => $resource,
            'action' => '*',
        ]);
    }

    /**
     * Helper for Action Wildcards (e.g., '*.approve')
     */
    public function actionWildcard(string $action)
    {
        return $this->state(fn () => [
            'slug' => "*.{$action}",
            'name' => 'Global ' . Str::headline($action),
            'resource' => '*',
            'action' => $action,
        ]);
    }
}