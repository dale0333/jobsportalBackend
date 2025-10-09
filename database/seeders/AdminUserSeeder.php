<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        if (DB::table('users')->where('email', 'admin@cdc.gov.ph')->doesntExist()) {
            DB::table('users')->insert([
                'user_type' => 'admin',
                'name' => 'CDC Administrator',
                'email' => 'admin@cdc.gov.ph',
                'email_verified_at' => now(),
                'password' => Hash::make('Admin123##'),
                'address' => 'Clark Freeport Zone, Pampanga, Philippines',
                'telephone' => '+63 912 345 6789',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: admin@cdc.gov.ph');
            $this->command->info('Password: Admin123##');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}
