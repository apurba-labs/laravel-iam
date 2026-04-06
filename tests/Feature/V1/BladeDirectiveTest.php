<?php
namespace ApurbaLabs\IAM\Tests\Feature\V1;

use ApurbaLabs\IAM\Tests\TestCase;
use ApurbaLabs\IAM\Tests\Support\Models\User;
use ApurbaLabs\IAM\Models\Role;
use ApurbaLabs\IAM\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

class BladeDirectiveTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_render_iam_directive()
    {
        $user = $this->createUserWithRole('manager', 101, ['invoice.approve']);
        $this->actingAs($user);

        // Test CASE 1: Has Permission
        $blade = "@iam('invoice.approve', 101) YES @else NO @endiam";
        $output = Blade::render($blade);
        $this->assertEquals("YES", trim($output));

        // Test CASE 2: Wrong Scope
        $bladeWrongScope = "@iam('invoice.approve', 999) YES @else NO @endiam";
        $outputWrong = Blade::render($bladeWrongScope);
        $this->assertEquals("NO", trim($outputWrong));
    }
    /**
     * Helper to create a user and assign a role in one line.
     */
    protected function createUserWithRole(string $roleName, $scopeId = null, array $permissions = [])
    {
        // Create User
        $userModel = config('auth.providers.users.model');
        $user = $userModel::create([
            'name' => 'Apurba Test',
            'email' => 'test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create Role (Check your iam_roles table - if it has no slug, remove it here too)
        $role = Role::firstOrCreate(['name' => $roleName]);

        // Create & Attach Permissions
        foreach ($permissions as $pName) {
            // Split 'invoice.approve' into resource and action
            $parts = explode('.', $pName);
            $permission = Permission::firstOrCreate(
                ['slug' => $pName],
                [
                    'name' => Str::headline(str_replace('.', ' ', $pName)),
                    'resource' => $parts[0] ?? null,
                    'action'   => $parts[1] ?? null,
                ]
            );

            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $user->assignRole($role, $scopeId);

        return $user;
    }
}