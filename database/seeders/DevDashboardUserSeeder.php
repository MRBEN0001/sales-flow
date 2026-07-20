<?php

namespace Database\Seeders;

use App\Models\DevUser;
use Illuminate\Database\Seeder;

class DevDashboardUserSeeder extends Seeder
{
    /**
     * Seeds the dev dashboard login into the central `dev_users` table.
     *
     * Email comes from config/dev.php; password is hardcoded here.
     */
    public function run(): void
    {
        $email = config('dev.dashboard.email');

        if (! $email) {
            return;
        }

        DevUser::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => config('dev.dashboard.name', 'SalesFlow Admin'),
                'password' => bcrypt('11111111'),
            ]
        );
    }
}
