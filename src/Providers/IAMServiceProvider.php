<?php

namespace ApurbaLabs\IAM\Providers;

use Illuminate\Support\ServiceProvider;

use ApurbaLabs\IAM\IAMManager;
use ApurbaLabs\IAM\Middleware\CheckPermission;
use ApurbaLabs\IAM\Services\RBAC\PermissionResolver;
use ApurbaLabs\IAM\Services\RBAC\ResourceRegistry;
use ApurbaLabs\IAM\Services\RBAC\ActionRegistry;

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
    }
}