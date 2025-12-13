<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // ==================== ADMIN ====================
        if (DB::table('users')->where('email', 'admin@test.com')->doesntExist()) {
            DB::table('users')->insert([
                'user_type' => 'admin',
                'name' => 'System Administrator',
                'email' => 'admin@test.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'address' => 'Clark Freeport Zone, Pampanga, Philippines',
                'telephone' => '+63 912 345 6789',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✅ Admin user created: admin@test.com / password123');
        } else {
            $this->command->info('ℹ️ Admin user already exists.');
        }
    }
}
