<?php

namespace ApurbaLabs\IAM\Providers;

use Illuminate\Support\ServiceProvider;
use ApurbaLabs\IAM\Middleware\CheckPermission;

class IAMServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('iam', function ($app) {
            return new \ApurbaLabs\IAM\IAM();
        });

        $this->app['router']->aliasMiddleware('iam', CheckPermission::class);
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}