<?php
namespace ApurbaLabs\IAM\Services\RBAC;

class ResourceRegistry
{
    protected array $resources = [];

    public function register(string $slug, string $label = null): void
    {
        $this->resources[$slug] = $label ?: ucfirst($slug);
    }

    public function all(): array
    {
        return $this->resources;
    }
}