<?php
namespace ApurbaLabs\IAM\Traits;
use ApurbaLabs\IAM\Models\Role;

trait HasRoles
{
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'iam_user_role'
        );
    }

    public function assignRole($roleName)
    {
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $this->roles()->syncWithoutDetaching($role);
        }
    }

    public function permissions()
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }
}