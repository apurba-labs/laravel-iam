<?php

namespace ApurbaLabs\IAM\Console\Commands;

use Illuminate\Console\Command;
use ApurbaLabs\IAM\Models\Permission;

use ApurbaLabs\IAM\Services\RBAC\ActionRegistry;
use ApurbaLabs\IAM\Services\RBAC\ResourceRegistry;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'iam:sync-permissions {--dry-run : Only show what will be created without saving}';

    /**
     * The console command description.
     */
    protected $description = 'Sync registered resources and actions into the iam_permissions table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $resources = app(ResourceRegistry::class)->all();
        $actions = app(ActionRegistry::class)->all();

        if (empty($resources)) {
            $this->warn("No resources registered. Use IAM::registerResources() in your AppServiceProvider.");
            return;
        }

        $this->info("Scanning " . count($resources) . " resources and " . count($actions) . " actions...");

        $count = 0;

        foreach ($resources as $slug => $label) {
            foreach ($actions as $action) {
                $permissionName = "{$slug}.{$action}";
                $description = "Allow " . ucfirst($action) . " on " . ($label ?: ucfirst($slug));

                if ($this->option('dry-run')) {
                    $this->line("<info>[Dry-Run]</info> Would create: <comment>{$permissionName}</comment>");
                    continue;
                }

                // Create or Update the permission
                Permission::firstOrCreate(
                    ['name' => $permissionName],
                    ['description' => $description]
                );

                $count++;
            }
        }

        if (!$this->option('dry-run')) {
            $this->info("Successfully synced {$count} permissions to the database!");
        } else {
            $this->info("Dry-run complete. No database changes were made.");
        }
    }
}