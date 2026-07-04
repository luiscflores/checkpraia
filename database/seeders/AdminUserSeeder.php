<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => config('app.admin_email', 'admin@checkpraia.pt')],
            [
                'name' => config('app.admin_name', 'Admin'),
                'username' => config('app.admin_username', 'admin'),
                'password' => bcrypt(config('app.admin_password', Str::random(16))),
                'is_admin' => true,
                'referral_code' => strtoupper(Str::random(8)),
            ]
        );

        $this->command->info('Admin user created: ' . config('app.admin_email', 'admin@checkpraia.pt'));
    }
}
