<?php
namespace ApurbaLabs\IAM\RBAC\Services;

class PermissionResolver
{
    public function can($user, string $permission): bool
    {
        $permissions = $user->permissions()->pluck('name');

        // Exact match
        if ($permissions->contains($permission)) {
            return true;
        }

        [$resource, $action] = explode('.', $permission);

        // Resource wildcard
        if ($permissions->contains("$resource.*")) {
            return true;
        }

        // Global wildcard
        if ($permissions->contains("*.*")) {
            return true;
        }

        // Manage rule
        if ($permissions->contains("$resource.manage")) {
            return true;
        }

        return false;
    }

    public function usersWithPermission(string $permission)
    {
        [$resource, $action] = explode('.', $permission);

        $userModel = config('auth.providers.users.model');

        return $userModel::whereHas('roles.permissions', function ($q) use ($permission, $resource) {
            $q->where('name', $permission)
            ->orWhere('name', "$resource.*")
            ->orWhere('name', "$resource.manage")
            ->orWhere('name', '*.*');
        })->get();
    }
}