<?php
namespace ApurbaLabs\IAM\Database\Factories;

use ApurbaLabs\IAM\Models\Scope;
use Illuminate\Database\Eloquent\Factories\Factory;

// A simple dummy model for testing
class TestScope extends Model { 
    protected $table = 'iam_scopes'; 
    protected $guarded = [];
}
/**
 * Only needed to simulate the SaaS environment for tests.
 * In a real SaaS environment, these scopes would be generated dynamically based on the tenant's context (e.g., organization_id, branch_id, team_id).
 */
class ScopeFactory extends Factory
{
    protected $model = TestScope::class;

    public function definition()
    {
        return [
            'type' => $this->faker->randomElement(['organization', 'branch', 'team']),
            'value' => (string) $this->faker->unique()->numberBetween(1, 9999),
        ];
    }
}