<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // AdminUserSeeder::class,
            JobConfigSeeder::class,
            EmployerSeeder::class
            // JobVacancySeeder::class,
            // AllUsersSeeder::class,
            // LocatorSeeder::class
        ]);
    }
}
