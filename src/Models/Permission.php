<?php

namespace ApurbaLabs\IAM\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'iam_permissions';

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'iam_role_permission');
    }
}
