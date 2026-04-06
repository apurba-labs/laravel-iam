<?php

namespace ApurbaLabs\IAM\Tests\Feature\V1;

use ApurbaLabs\IAM\Tests\TestCase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;

use ApurbaLabs\IAM\Tests\Support\Models\User;
use ApurbaLabs\IAM\Models\Role;
use ApurbaLabs\IAM\Models\Permission;

class AuthorizationComparisonTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_verifies_both_native_can_and_package_can_iam_work_identically()
    {
        // Setup: Define credentials
        $roleName = 'manager';
        $scopeId = 505;
        $permissionStr = 'invoice.approve';
        
        // Create User using the refined helper
        $user = $this->createUserWithPermission($roleName, $scopeId, [$permissionStr]);
        
        // Test Package Method (Direct Call via Trait)
        $this->assertTrue($user->canIam($permissionStr, $scopeId), "Direct canIam() failed.");

        // Test Native Laravel Method (Via Gate::before Bridge)
        $this->assertTrue($user->can($permissionStr, $scopeId), "Native can() failed to bridge to IAM.");

        // Test Native Gate Facade (Global Access)
        $this->assertTrue(Gate::forUser($user)->allows($permissionStr, $scopeId), "Gate::allows failed.");

        // Test Failure Case (Wrong Scope)
        $wrongScope = 999;
        $this->assertFalse($user->canIam($permissionStr, $wrongScope), "canIam should fail for wrong scope.");
        $this->assertFalse($user->can($permissionStr, $wrongScope), "Native can() should fail for wrong scope.");
    }

    /**
     * Helper to create a user and assign a role with permissions.
     */
    protected function createUserWithPermission(string $roleName, $scopeId = null, array $permissions = [])
    {
        $userModel = config('auth.providers.users.model');
        
        $user = $userModel::create([
            'name'     => 'Apurba Test',
            'email'    => 'test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create/Get Role
        $role = Role::firstOrCreate(
            ['slug' => \Illuminate\Support\Str::slug($roleName)],
            ['name' => $roleName]
        );

        // Create & Sync Permissions
        foreach ($permissions as $pName) {
            $parts = explode('.', $pName);
            $resource = $parts[0] ?? '*';
            $action   = $parts[1] ?? '*';

            $permission = Permission::firstOrCreate([
                'name'     => $pName,
                'resource' => $resource,
                'action'   => $action,
            ]);

            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        // Assign Role to User with Scope
        $user->assignRole($role, $scopeId);

        return $user;
    }
}