<?php

namespace App\Listeners;

use Database\Seeders\TenantProvisioningSeeder;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Events\DatabaseMigrated;

class SeedTenantDatabase
{
    public function handle(DatabaseMigrated $event): void
    {
        $event->tenant->run(function () {
            Artisan::call('db:seed', [
                '--class' => TenantProvisioningSeeder::class,
                '--force' => true,
            ]);
        });
    }
}
