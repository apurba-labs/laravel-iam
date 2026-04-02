<?php
namespace ApurbaLabs\IAM\Tests\Feature\V1;

use ApurbaLabs\IAM\Tests\TestCase;
use ApurbaLabs\IAM\Tests\Support\Models\User;
use ApurbaLabs\IAM\Models\Role;
use ApurbaLabs\IAM\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiPermissionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_blocks_access_if_user_lacks_middleware_permission()
    {
        $user = User::create(['name' => 'Staff', 'email' => 'staff@test.com', 'password' => '123']);
        
        // No roles assigned yet
        $response = $this->actingAs($user)
                ->getJson('/test/invoices');

        $response->assertStatus(403); // Middleware should block this
    }

    /** @test */
    public function it_allows_access_when_scoped_permission_is_assigned()
    {
        // 1. Setup
        $user = User::create(['name' => 'Manager', 'email' => 'mgr@test.com', 'password' => '123']);
        $role = Role::create(['name' => 'Approver', 'slug' => 'approver']);
        $perm = Permission::create(['name' => 'invoice.approve']);
        $role->permissions()->attach($perm);

        // Assign role ONLY for Branch 101
        $user->assignRole('approver', 101);

        // 2. Request for Branch 101 (Should Pass)
        $response1 = $this->actingAs($user)
            ->withHeader('X-Scope-ID', 101)
            ->postJson('/test/invoices/1/approve');

        $response1->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Invoice 1 approved in scope 101']);

        // 3. Request for Branch 202 (Should Fail)
        $response2 = $this->actingAs($user)
            ->withHeader('X-Scope-ID', 202)
            ->postJson('/test/invoices/1/approve');

        $response2->assertStatus(403);
    }
}