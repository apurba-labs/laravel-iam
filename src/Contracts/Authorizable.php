<?php
namespace ApurbaLabs\IAM\Contracts;

interface Authorizable
{
    public function canIam(string $permission, $scopeId = null): bool;
}