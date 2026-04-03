<?php

namespace ApurbaLabs\IAM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ApurbaLabs\IAM\Database\Factories\RoleFactory;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $table = 'iam_roles';

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'iam_role_permission');
    }

    public function users()
    {
        $userModel = config('auth.providers.users.model');
        return $this->belongsToMany($userModel, 'iam_user_role')
            ->withPivot('scope_id');
    }

    protected static function newFactory()
    {
        return RoleFactory::new();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            if (empty($role->slug)) {
                $role->slug = Str::slug($role->name);
            }
        });
    }
}

