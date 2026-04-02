<?php
namespace ApurbaLabs\IAM\Traits;

use ApurbaLabs\IAM\Models\Role;
use ApurbaLabs\IAM\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

trait HasRoles
{
    /**
     * Relationship to Roles with Pivot Data (scope_id)
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'iam_user_role')
                    ->withPivot('scope_id');
    }

    /**
     * Assign a role to a user within a specific scope.
     */
    public function assignRole($roleIdentifier, $scopeId = null)
    {
        // Try to find by slug first, then name
        $role = Role::where('slug', $roleIdentifier)
                    ->orWhere('name', $roleIdentifier)
                    ->first();

        if ($role) {
            $this->roles()->syncWithoutDetaching([
                $role->id => ['scope_id' => $scopeId]
            ]);
            
            $this->flushIAMCache();
        }
    }

    /**
     * Get unique permissions for this user, filtered by scope.
     * This uses a single query instead of flattening in PHP.
     */
    public function permissions($scopeId = null)
    {
        return Permission::query()
            ->whereHas('roles.users', function ($q) use ($scopeId) {
                $q->where('users.id', $this->id)
                  ->when($scopeId, function ($sq) use ($scopeId) {
                      $sq->where('iam_user_role.scope_id', $scopeId);
                  });
            })
            ->get();
    }

    /**
     * Helper to clear the IAM cache for this specific user.
     */
    public function flushIAMCache(): void
    {
        Cache::forget("iam_permissions_user_{$this->id}");
    }
}