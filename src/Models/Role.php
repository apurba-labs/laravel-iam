<?php

namespace ApurbaLabs\IAM\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'iam_roles';

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'iam_role_permission');
    }
}
