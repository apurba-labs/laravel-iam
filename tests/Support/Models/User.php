<?php
namespace ApurbaLabs\IAM\Tests\Support\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use ApurbaLabs\IAM\Contracts\Authorizable;
use ApurbaLabs\IAM\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ApurbaLabs\IAM\Tests\Database\Factories\UserFactory;

class User extends Authenticatable implements Authorizable
{
    use HasFactory, HasRoles;

    // For testing purposes, we can allow mass assignment on all fields
    protected $guarded = [];

    /**
     * Implementation of the Contract
     * We wrap your service logic here
     */
    public function canIam(string $permission, $scopeId = null): bool
    {
        return app('iam')->can($this, $permission, $scopeId);
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}