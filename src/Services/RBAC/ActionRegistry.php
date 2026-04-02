<?php
namespace ApurbaLabs\IAM\Services\RBAC;

class ActionRegistry
{
    protected array $actions = [];

    public function __construct()
    {
        // Initialize with the standard package actions
        $this->actions = PermissionActions::all();
    }

    public function register(array $newActions): void
    {
        // Merge custom actions and ensure no duplicates
        $this->actions = array_unique(array_merge($this->actions, $newActions));
    }

    public function all(): array
    {
        return $this->actions;
    }
}