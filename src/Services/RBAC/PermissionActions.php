<?php
namespace ApurbaLabs\IAM\Services\RBAC;

class PermissionActions
{
    // Common CRUD
    public const CREATE = 'create';
    public const READ   = 'read';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    public const LIST   = 'list';

    // SaaS / Workflow Actions
    public const PUBLISH = 'publish';
    public const APPROVE = 'approve';
    public const REJECT  = 'reject';
    public const REFUND  = 'refund';
    
    // System Actions
    public const MANAGE  = 'manage'; // Special: covers all actions
    public const ALL     = '*';      // The Ultimate Wildcard

    /**
     * Helper to get all defined actions for UI dropdowns
     */
    public static function all(): array
    {
        return (new \ReflectionClass(static::class))->getConstants();
    }
}