<?php
namespace ApurbaLabs\IAM;

use ApurbaLabs\IAM\Services\RBAC\ResourceRegistry;
use ApurbaLabs\IAM\Services\RBAC\ActionRegistry;
use ApurbaLabs\IAM\Services\RBAC\PermissionResolver;
use ApurbaLabs\IAM\Contracts\Authorizable;

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
     * The Gateway for checking permissions.
     * This is what IAM::can() calls.
     */
    public function can(Authorizable $user, string $permission, $scopeId = null): bool
    {
        // We call 'can' on the resolver, not 'resolve'
        return $this->resolver->can($user, $permission, $scopeId);
    }

    /**
     * Find users for the Approval Engine.
     */
    public function usersWithPermission(string $permission, $scopeId = null)
    {
        return $this->resolver->usersWithPermission($permission, $scopeId);
    }

    /**
     * @method static \Illuminate\Support\Collection usersWithRole(string $role, $scopeId = null)
     */
    public function usersWithRole(string $role, $scopeId = null)
    {
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
     * For developers to register their modules.
     */
    public function registerResources(array $resources): void
    {
        foreach ($resources as $slug => $label) {
            $this->resourceRegistry->register($slug, $label);
        }
    }

    /**
     * For developers to register custom actions like 'submit', 'approve'
     */
    public function registerActions(array $actions): void
    {
        $this->actionRegistry->register($actions);
    }
}