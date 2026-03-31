<?php
namespace ApurbaLabs\IAM\Contracts;

interface Authorizable
{
    public function can(string $permission): bool;
}