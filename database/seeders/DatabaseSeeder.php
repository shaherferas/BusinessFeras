<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'phone_number' => '+12351826860',
                'whatsapp_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_business_owner' => true,
                'active_mode' => 'business',
            ],
        );

        $user->syncRoles(['Super Admin', 'Business Owner']);
    }
}
