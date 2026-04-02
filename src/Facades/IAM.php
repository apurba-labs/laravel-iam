<?php

namespace ApurbaLabs\IAM\Facades;

use Illuminate\Support\Facades\Facade;

class IAM extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'iam';
    }
}