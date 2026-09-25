<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantProvisioningSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $shopName = $tenant->shop_name ?: 'My Pharmacy';
        $adminName = $tenant->admin_name ?: 'Admin';
        $adminEmail = $tenant->admin_email ?: $tenant->owner_email;
        $adminPassword = $tenant->admin_password;

        if (! $adminEmail || ! $adminPassword) {
            return;
        }

        DB::table('setting')->insert([
            'id_setting' => 1,
            'nama_perusahaan' => $shopName,
            'deskripsi_perusahaan' => null,
            'alamat' => '',
            'telepon' => '',
            'tipe_nota' => 1,
            'diskon' => 0,
            'path_logo' => '/img/logo.png',
            'path_kartu_member' => '/img/member.png',
        ]);

        User::query()->updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => $adminPassword,
                'foto' => '/img/user.jpg',
                'level' => 1,
            ]
        );

        $this->call([
            DevShopsAdminUserSeeder::class,
            UserTableSeeder::class,
        ]);

        tenancy()->central(function () use ($tenant) {
            $record = Tenant::find($tenant->id);
            if (! $record) {
                return;
            }

            $data = $record->data ?? [];
            unset($data['admin_password']);
            $record->data = $data;
            $record->save();
        });
    }
}
