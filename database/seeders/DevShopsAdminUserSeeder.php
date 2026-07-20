<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DevShopsAdminUserSeeder extends Seeder
{
    /**
     * Seeds the global dev admin into the CURRENT tenant's database.
     * Must be run inside a tenant context (tenancy initialized).
     *
     * Email comes from config/dev.php; password is hardcoded here.
     */
    public function run(): void
    {
        $email = config('dev.admin.email');

        if (! $email) {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => config('dev.admin.name', 'SalesFlow Support'),
                'password' => bcrypt('11111111'),
                'foto' => '/img/user.jpg',
                'level' => 1,
            ]
        );
    }
}
