<?php

namespace ApurbaLabs\IAM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ApurbaLabs\IAM\Database\Factories\PermissionFactory;

class Permission extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'resource', 'action', 'description'];

    protected $table = 'iam_permissions';

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'iam_role_permission');
    }

    protected static function newFactory()
    {
        return PermissionFactory::new();
    }

    protected static function booted()
    {
        static::creating(function ($permission) {
            // If the developer only provided 'name' (e.g., 'post.view')
            // we split it and fill the 'resource' and 'action' columns automatically.
            if (str_contains($permission->name, '.')) {
                [$resource, $action] = explode('.', $permission->name, 2);
                $permission->resource = $permission->resource ?? $resource;
                $permission->action = $permission->action ?? $action;
            }
        });
    }
    
}
