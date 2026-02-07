<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesSeeder::class);
        $this->call(SettingsSeeder::class);
        // $this->call(CategoriesSeeder::class);
        // $this->call(ServicesSeeder::class);

        $user = User::firstOrCreate(
            ['email' => 'info@n-tawasull.sa'],
            [
                'name' => 'N-Tawasull (Super Admin)',
                'password' => 'Password',
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole('super-admin');
    }
}
