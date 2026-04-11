<?php

namespace ApurbaLabs\IAM;

use Illuminate\Support\Collection;
use ApurbaLabs\IAM\Contracts\Authorizable;
use ApurbaLabs\IAM\Services\RBAC\ActionRegistry;
use ApurbaLabs\IAM\Services\RBAC\ResourceRegistry;
use ApurbaLabs\IAM\Services\RBAC\PermissionResolver;

class IAMManager
{
    protected $resourceRegistry;
    protected $actionRegistry;
    protected $resolver;

    public function __construct(
        ResourceRegistry $resourceRegistry,
        ActionRegistry $actionRegistry,
        PermissionResolver $resolver
    ) {
        $this->resourceRegistry = $resourceRegistry;
        $this->actionRegistry = $actionRegistry;
        $this->resolver = $resolver;
    }

    /**
     * Check if user has permission.
     */
    public function can(
        Authorizable $user,
        string $permission,
        $scopeId = null
    ): bool {
        return $this->resolver->can($user, $permission, $scopeId);
    }

    /**
     * Find users with permission.
     */
    public function usersWithPermission(
        string $permission,
        $scopeId = null
    ): Collection {
        return $this->resolver->usersWithPermission(
            $permission,
            $scopeId
        );
    }

    /**
     * Find users with role.
     */
    public function usersWithRole(
        string $role,
        $scopeId = null
    ): Collection {
        $userModel = config('auth.providers.users.model');

        return $userModel::query()
            ->whereHas('roles', function ($query) use ($role, $scopeId) {
                $query->where('slug', $role);

                if ($scopeId) {
                    $query->wherePivot('scope_id', $scopeId);
                }
            })
            ->get();
    }

    /**
     * Get all roles assigned to user.
     */
    public function rolesForUser(
        Authorizable $user
    ): Collection {
        return method_exists($user, 'roles')
            ? $user->roles()->get()
            : collect();
    }

    /**
     * Get all direct/effective permissions for user.
     *
     * NOTE: Basic implementation for now.
     * Can evolve later for wildcard/expanded permissions.
     */
    public function permissionsForUser(
        Authorizable $user
    ): Collection {
        if (!method_exists($user, 'roles')) {
            return collect();
        }

        return $user->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id')
            ->values();
    }

    /**
     * Register resources.
     */
    public function registerResources(array $resources): void
    {
        foreach ($resources as $slug => $label) {
            $this->resourceRegistry->register($slug, $label);
        }
    }

    /**
     * Register custom actions.
     */
    public function registerActions(array $actions = []): void
    {
        if (empty($actions)) {
            $actions = \ApurbaLabs\IAM\Services\RBAC\PermissionActions::all();
        }
        $this->actionRegistry->register($actions);
    }
}