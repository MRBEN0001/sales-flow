<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Database\Seeders\DevShopsAdminUserSeeder;
use Illuminate\Console\Command;

class SeedDevAdmin extends Command
{
    protected $signature = 'dev:seed-admin {tenant? : A specific tenant id; omit to run for all}';

    protected $description = 'Seed the global dev admin into every existing shop (or one shop).';

    public function handle(): int
    {
        $tenants = $this->argument('tenant')
            ? Tenant::where('id', $this->argument('tenant'))->get()
            : Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn('No matching shops found.');

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            try {
                $tenant->run(function () {
                    (new DevShopsAdminUserSeeder())->setContainer(app())->run();
                });
                $this->info("Dev admin seeded into: {$tenant->id}");
            } catch (\Throwable $e) {
                $this->error("Failed for {$tenant->id}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
