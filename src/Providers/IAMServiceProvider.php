<?php

namespace ApurbaLabs\IAM\Providers;

use Illuminate\Support\ServiceProvider;

use ApurbaLabs\IAM\IAMManager;
use ApurbaLabs\IAM\Middleware\CheckPermission;
use ApurbaLabs\IAM\Services\RBAC\PermissionResolver;
use ApurbaLabs\IAM\Services\RBAC\ResourceRegistry;
use ApurbaLabs\IAM\Services\RBAC\ActionRegistry;
use Illuminate\Support\Facades\Blade;

class IAMServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind the Registry as a Singleton (The storage for resources)
        $this->app->singleton(ResourceRegistry::class);

        // Bind the Resolver to the Service Container
        $this->app->singleton('iam', function ($app) {
            return new IAMManager(
                $app->make(ResourceRegistry::class),
                $app->make(ActionRegistry::class),
                new PermissionResolver()
            );
        });
        // Register the Middleware Alias
        $this->app['router']->aliasMiddleware('iam', CheckPermission::class);
    }

    public function boot()
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \ApurbaLabs\IAM\Console\Commands\SyncPermissions::class,
            ]);
        }
        // Register Blade Directives
        $this->registerBladeDirectives();
    }
    protected function registerBladeDirectives()
    {
        // 1. @iam('resource.action', $scopeId)
        // Checks if the user has the permission in a specific context.
        Blade::if('iam', function ($permission, $scopeId = null) {
            return auth()->check() && app('iam')->can(auth()->user(), $permission, $scopeId);
        });

        // 2. @role('manager', $scopeId)
        // Checks if the user has a specific role in a specific context.
        Blade::if('role', function ($role, $scopeId = null) {
            return auth()->check() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole($role, $scopeId);
        });
    }
}