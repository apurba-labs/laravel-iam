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
        $role = null;

        // If it's already a Role object, use it directly
        if ($roleIdentifier instanceof Role) {
            $role = $roleIdentifier;
        } 
        // If it's a numeric ID
        elseif (is_numeric($roleIdentifier)) {
            $role = Role::find($roleIdentifier);
        } 
        // If it's a String (Slug or Name)
        else {
            $role = Role::where('slug', $roleIdentifier)
                        ->orWhere('name', $roleIdentifier)
                        ->first();
        }

        if ($role) {
            // We use attach or syncWithoutDetaching with the pivot data
            $this->roles()->syncWithoutDetaching([
                $role->id => ['scope_id' => $scopeId]
            ]);
            
            $this->flushIAMCache();
            
            return $this; // Return $this for chaining
        }

        throw new \Exception("Role [{$roleIdentifier}] not found.");
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