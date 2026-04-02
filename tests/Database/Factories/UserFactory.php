<?php
namespace ApurbaLabs\IAM\Tests\Database\Factories;

use ApurbaLabs\IAM\Tests\Support\Models\User; // Use your Test User Model
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Fix: Combined forName and matching email logic.
     * This ensures the email is always unique based on the name.
     */
    public function forName(?string $name = null, string $domain = 'test.com'): self
    {
        return $this->state(function () use ($name, $domain) {
            // Generate a random name if none provided, or use the one passed
            $finalName = $name ?? $this->faker->unique()->firstName() . '_' . $this->faker->randomNumber(2);
            
            return [
                'name'  => $finalName,
                'email' => strtolower($finalName) . '@' . $domain,
            ];
        });
    }

    /**
     * Assign a role to the user, creating it only if it doesn't exist.
     */
    public function withRole(string $roleName, ?string $description = null): self
    {
        return $this->state(function () use ($roleName, $description) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['description' => $description ?? "System generated {$roleName} role"]
            );

            return [
                'role_id' => $role->id,
            ];
        });
    }

    /**
     * Keep this for cases where you need a very specific email regardless of name.
     */
    public function atEmail(string $email): self
    {
        return $this->state(['email' => $email]);
    }
}