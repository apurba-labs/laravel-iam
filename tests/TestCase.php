<?php
namespace ApurbaLabs\IAM\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use ApurbaLabs\IAM\Providers\IAMServiceProvider;
use ApurbaLabs\IAM\Database\Seeders\DatabaseSeeder;
use ApurbaLabs\IAM\Tests\Support\Models\User;

class TestCase extends Orchestra
{
    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = false;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            IAMServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        // Load migrations from the package root
        $this->loadMigrationsFrom(\Orchestra\Testbench\default_migration_path());

        // Load your Support/Mock migrations (Roles, Purchases, etc.)
        $this->loadMigrationsFrom(__DIR__ . '/Support/Migrations');

        // Load your package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

    }

    /**
     * Automatically seed each test with this seeder.
     * @return array<int,string>
     */
    protected function defineDatabaseSeeders()
    {
        return [
           DatabaseSeeder::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Use SQLite in memory for lightning-fast testing
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Config Laravel to use your Test User
        $app['config']->set('auth.providers.users.model', User::class);
        
        // Define the API guard (Standard for Testing)
        $app['config']->set('auth.guards.api', [
            'driver' => 'session', // Use session driver for easy testing
            'provider' => 'users',
        ]);

        //Load the Test Routes
        //$this->loadTestRoutes();
    }
    /**
     * Define routes setup.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        // This mimics how a developer would register routes in their web.php or api.php
        require __DIR__ . '/routes/test-api.php';
    }
}