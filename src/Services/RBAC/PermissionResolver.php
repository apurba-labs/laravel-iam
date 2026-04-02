<?php
namespace ApurbaLabs\IAM\Services\RBAC;
use Illuminate\Support\Facades\Cache;

class PermissionResolver
{
    /**
     * Get and cache permissions for a specific user and scope.
     */
    public function userPermissions($user, $scopeId = null): array
    {
        $cacheKey = "iam_permissions_user_{$user->id}_scope_" . ($scopeId ?? 'global');

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user, $scopeId) {
            // This calls the permissions() method in your HasRoles trait
            return $user->permissions($scopeId)->pluck('name')->toArray();
        });
    }

    /**
     * The core check: Does the user have the permission?
     */
    public function can($user, string $permission, $scopeId = null): bool
    {
        $permissions = $this->userPermissions($user, $scopeId);

        // Exact match (e.g. 'invoice.approve')
        if (in_array($permission, $permissions)) {
            return true;
        }

        // Wildcard match (e.g. 'invoice.*' or 'invoice.manage')
        if (str_contains($permission, '.')) {
            [$resource, $action] = explode('.', $permission);
            
            if (in_array("$resource.*", $permissions)) return true;
            if (in_array("$resource.manage", $permissions)) return true;
        }

        // Global admin match
        if (in_array("*.*", $permissions)) {
            return true;
        }

        return false;
    }

    /**
     * Find all users who have a specific permission in a specific scope.
     * Useful for the Approval Engine to find "Who can approve this?"
     */
    public function usersWithPermission(string $permission, $scopeId = null)
    {
        if (!str_contains($permission, '.')) return collect();

        [$resource, $action] = explode('.', $permission);
        $userModel = config('auth.providers.users.model');
        $userTable = (new $userModel)->getTable();

        $validPermissions = [$permission, "$resource.*", "$resource.manage", "*.*"];

        return $userModel::query()
            ->select("$userTable.*")
            ->distinct()
            // Join with Roles through the Pivot
            ->join('iam_user_role', "$userTable.id", "=", "iam_user_role.user_id")
            ->join('iam_roles', "iam_user_role.role_id", "=", "iam_roles.id")
            // Join Roles with Permissions
            ->join('iam_role_permission', "iam_roles.id", "=", "iam_role_permission.role_id")
            ->join('iam_permissions', "iam_role_permission.permission_id", "=", "iam_permissions.id")
            // Filter by Permission Names and Scope
            ->whereIn('iam_permissions.name', $validPermissions)
            ->when($scopeId, function ($query) use ($scopeId) {
                $query->where('iam_user_role.scope_id', $scopeId);
            })
            ->get();
    }
}