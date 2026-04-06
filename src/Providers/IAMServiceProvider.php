<?php

namespace ApurbaLabs\IAM\Providers;

use Illuminate\Support\ServiceProvider;

use ApurbaLabs\IAM\IAMManager;
use ApurbaLabs\IAM\Middleware\CheckPermission;
use ApurbaLabs\IAM\Services\RBAC\PermissionResolver;
use ApurbaLabs\IAM\Services\RBAC\ResourceRegistry;
use ApurbaLabs\IAM\Services\RBAC\ActionRegistry;
use ApurbaLabs\IAM\Contracts\Authorizable;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;


class IAMServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind the Registry as a Singleton (The storage for resources)
        $this->app->singleton(ResourceRegistry::class);
        $this->app->singleton(ActionRegistry::class);

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

        // Bridge to Laravel's native Gate system
        Gate::before(function ($user, $ability, $arguments) {

            if ($user instanceof Authorizable) {
                // Laravel passes extra arguments to the Gate as an array in $params
                // We take the first element as the scopeId
                // If we call $user->can('perm', 505), $arguments is [0 => 505]
                // We must extract the actual ID:
                $scopeId = is_array($arguments) ? ($arguments[0] ?? null) : $arguments;

                if (app('iam')->can($user, $ability, $scopeId)) {
                    return true;
                }
            }
            return null; // Fallback to standard policies if IAM doesn't match
        });
        
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