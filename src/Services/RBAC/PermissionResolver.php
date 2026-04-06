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
        // FORCE $scopeId to be a single value, never an array
        if (is_array($scopeId)) {
            $scopeId = $scopeId[0] ?? null;
        }

        $permissions = $this->userPermissions($user, $scopeId);

        /**
         * The "Four Levels of Truth" Strategy
         * We evaluate authority from the broadest to the most specific.
         */

        // Level 1: Global Authority (*.*)
        if (in_array("*.*", $permissions)) return true;

        // Level 2: Atomic Match (Direct Permission)
        if (in_array($permission, $permissions)) return true;

        // Level 3 & 4: Hierarchical Wildcards
        if (str_contains($permission, '.')) {
            [$resource, $action] = explode('.', $permission);
            
            // Level 3: Resource Authority (e.g., invoice.* or invoice.manage)
            if (in_array("{$resource}.*", $permissions)) return true;
            if (in_array("{$resource}.manage", $permissions)) return true;
            
            // Level 4: Action Authority (e.g., *.approve)
            if (in_array("*.{$action}", $permissions)) return true;
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

        //valid set to include action-based wildcards
        $validPermissions = [
            $permission, 
            "{$resource}.*", 
            "{$resource}.manage", 
            "*.{$action}",
            "*.*"
        ];

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
            ->where(function ($query) use ($scopeId) {
                $query->where('iam_user_role.scope_id', $scopeId);
                // allow Global Admins to show up too
                $query->orWhereNull('iam_user_role.scope_id');
            })
            ->get();
    }
}