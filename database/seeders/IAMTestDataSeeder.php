<?php
namespace ApurbaLabs\IAM\Database\Seeders;

use Illuminate\Database\Seeder;
use ApurbaLabs\IAM\Models\Role;
use ApurbaLabs\IAM\Models\Permission;

class IAMTestDataSeeder extends Seeder
{
    public function run()
    {
        // Create Core Permissions
        $p1 = Permission::create(['name' => 'invoice.approve']);
        $p2 = Permission::create(['name' => 'invoice.*']); // Wildcard
        $p3 = Permission::create(['name' => '*.*']);       // Super Admin

        // Create Roles
        $admin = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $manager = Role::create(['name' => 'Manager', 'slug' => 'manager']);

        // Attach Permissions to Roles
        $admin->permissions()->attach([$p3->id]); // Admin gets everything
        $manager->permissions()->attach([$p1->id]); // Manager only gets approve
    }
}