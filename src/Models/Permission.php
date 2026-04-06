<?php

namespace ApurbaLabs\IAM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ApurbaLabs\IAM\Database\Factories\PermissionFactory;
use ApurbaLabs\IAM\Exceptions\InvalidPermissionException;
use Illuminate\Support\Str;

class Permission extends Model
{
    use HasFactory;
    protected $fillable = ['slug', 'name', 'resource', 'action', 'description'];

    protected $table = 'iam_permissions';

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'iam_role_permission');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function newFactory()
    {
        return PermissionFactory::new();
    }

    protected static function booted()
    {
        static::creating(function ($permission) {
            /**
             * We now use 'slug' (e.g., 'invoice.approve') to derive resource and action.
             * If 'name' is empty, we prettify the slug for the UI.
             */
            
            if (empty($permission->slug)) {
                throw InvalidPermissionException::slugRequired(); // slug is mandatory now
            }

            // Split Slug for Resource & Action
            if (str_contains($permission->slug, '.')) {
                [$resource, $action] = explode('.', $permission->slug, 2);
                $permission->resource = $permission->resource ?? $resource;
                $permission->action = $permission->action ?? $action;
            }

            // Auto-generate Name for UI if not provided
            // e.g., 'invoice.approve' -> 'Invoice Approve'
            if (empty($permission->name)) {
                $permission->name = Str::headline(str_replace('.', ' ', $permission->slug));
            }
        });
    }
    
}
