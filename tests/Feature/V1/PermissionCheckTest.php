<?php

namespace ApurbaLabs\IAM\Tests\Feature\V1;

use ApurbaLabs\IAM\Tests\TestCase;
use ApurbaLabs\IAM\Tests\Support\Models\User;
use ApurbaLabs\IAM\Models\Role;
use ApurbaLabs\IAM\Models\Permission;
use ApurbaLabs\IAM\Models\Scope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ApurbaLabs\IAM\Facades\IAM;

class PermissionCheckTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_resolve_complex_saas_permissions()
    {
        // 1. Setup Data
        $user = User::create(['name' => 'Apurba', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
        $branchA = Scope::create(['type' => 'branch', 'value' => '101']);
        $branchB = Scope::create(['type' => 'branch', 'value' => '202']);

        // Create Roles & Permissions
        $editorRole = Role::create(['name' => 'Editor', 'slug' => 'editor']);
        $viewPermission = Permission::create(['slug' => 'post.view']);
        $wildcardPermission = Permission::create(['slug' => 'post.*']);

        // Attach Permissions to Role
        $editorRole->permissions()->attach([$viewPermission->id, $wildcardPermission->id]);

        // 2. Assign Role to User ONLY for Branch A
        $user->assignRole('editor', $branchA->id);

        // --- THE TESTS ---

        // Test A: User should have permission in Branch A
        $this->assertTrue(IAM::can($user, 'post.view', $branchA->id), "User should have view access in Branch A");
        
        // Test B: Wildcard check (post.delete is covered by post.*)
        $this->assertTrue(IAM::can($user, 'post.delete', $branchA->id), "User should have delete access via wildcard in Branch A");

        // Test C: Scope Isolation (User should NOT have permission in Branch B)
        $this->assertFalse(IAM::can($user, 'post.view', $branchB->id), "User should NOT have access in Branch B");

        // Test D: Reverse Lookup (Find all users who can view posts in Branch A)
        $usersFound = IAM::usersWithPermission('post.view', $branchA->id);
        $this->assertCount(1, $usersFound);
        $this->assertEquals('Apurba', $usersFound->first()->name);
    }
}