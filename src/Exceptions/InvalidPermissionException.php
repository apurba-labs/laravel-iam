<?php

namespace ApurbaLabs\IAM\Exceptions;

use Exception;

class InvalidPermissionException extends Exception
{
    public static function slugRequired(): self
    {
        return new static("A unique 'slug' is required to create a Permission (e.g., 'invoice.view').");
    }
}