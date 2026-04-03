<?php
namespace ApurbaLabs\IAM\Tests\Feature\V1;

use ApurbaLabs\IAM\Tests\TestCase;
use ApurbaLabs\IAM\Tests\Support\Models\User;
use ApurbaLabs\IAM\Models\Role;
use ApurbaLabs\IAM\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WorkflowResolutionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_resolve_users_for_a_specific_workflow_step_in_a_scope()
    {
        // Setup: Create two branches (Scopes)
        $dhakaBranchId = 101;
        $ctgBranchId = 102;

        // Setup: Create users with specific roles in specific branches
        $dhakaManager = $this->createUserWithRole('manager', $dhakaBranchId, ['invoice.approve']);
        $ctgManager = $this->createUserWithRole('manager', $ctgBranchId, ['invoice.approve']);
        $globalAdmin = $this->createUserWithRole('admin', null, ['invoice.approve']); // No scope = Global

        //$check = \Illuminate\Support\Facades\DB::table('iam_user_role')
        //    ->where('scope_id', 101)
        //    ->exists();
        //dd($check);
        // Action: An invoice needs approval in DHAKA (101)
        // We need to find everyone who can 'invoice.approve' there.
        //\Illuminate\Support\Facades\DB::enableQueryLog();
        $approvers = \ApurbaLabs\IAM\Facades\IAM::usersWithPermission('invoice.approve', $dhakaBranchId);
        //dd(\Illuminate\Support\Facades\DB::getQueryLog());
        if ($approvers->isEmpty()) {
            $allPermissions =Permission::all()->pluck('name')->toArray();
            $allRoles = Role::with('permissions')->get()->toArray();
            
            // This will stop the test and show you exactly what is in the DB
            dd([
                'Searching For' => 'invoice.approve',
                'Scope ID' => $dhakaBranchId,
                'Permissions in DB' => $allPermissions,
                'Roles & Their Permissions' => $allRoles,
                'Found Approvers' => $approvers->toArray()
            ]);
        }
        // Assertion: 
        // - Dhaka Manager should be there.
        // - Global Admin should be there (if your logic allows global fallback).
        // - Chittagong Manager should NOT be there.
        $this->assertTrue($approvers->contains('id', $dhakaManager->id));
        $this->assertFalse($approvers->contains('id', $ctgManager->id));
        
        // This proves your "Four Levels of Truth" works for database queries too!
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
                ['name' => $pName],
                [
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