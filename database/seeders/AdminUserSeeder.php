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

        // ==================== SECRETARIAT ====================
        if (DB::table('users')->where('email', 'secretariat@test.com')->doesntExist()) {
            DB::table('users')->insert([
                'user_type' => 'secretariat',
                'name' => 'Secretariat Office',
                'email' => 'secretariat@test.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'address' => 'Clark Freeport Zone, Pampanga, Philippines',
                'telephone' => '+63 923 456 7890',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✅ Secretariat user created: secretariat@test.com / password123');
        } else {
            $this->command->info('ℹ️ Secretariat user already exists.');
        }

        // ==================== EMPLOYER ====================
        if (DB::table('users')->where('email', 'employer@test.com')->doesntExist()) {
            $userId = DB::table('users')->insertGetId([
                'user_type' => 'employer',
                'name' => 'TechWorks Corporation',
                'email' => 'employer@test.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'telephone' => $faker->phoneNumber,
                'address' => $faker->address,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('employers')->insert([
                'user_id' => $userId,
                'company_size' => '51-200',
                'industry' => 'Technology Industry',
                'locator_number' => '123456',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✅ Employer created: employer@test.com / password123');
        } else {
            $this->command->info('ℹ️ Employer user already exists.');
        }

        // ==================== JOB SEEKER ====================
        if (DB::table('users')->where('email', 'jobseeker@test.com')->doesntExist()) {
            $userId = DB::table('users')->insertGetId([
                'user_type' => 'job_seeker',
                'name' => 'Juan Dela Cruz',
                'email' => 'jobseeker@test.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'telephone' => $faker->phoneNumber,
                'address' => $faker->address,
                'bio' => 'A dedicated and skilled professional seeking new opportunities.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('job_seekers')->insert([
                'user_id' => $userId,
                'date_of_birth' => '1995-03-15',
                'gender' => 'male',
                'education_level' => 'Bachelor\'s Degree',
                'field_of_study' => 'Computer Science',
                'skills' => json_encode(['PHP', 'JavaScript', 'Laravel', 'MySQL']),
                'services' => json_encode($faker->randomElements(range(1, 20), $faker->numberBetween(2, 4))),
                'years_of_experience' => 3,
                'preferred_location' => 'Manila',
                'expected_salary' => 35000,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✅ Job Seeker created: jobseeker@test.com / password123');
        } else {
            $this->command->info('ℹ️ Job Seeker user already exists.');
        }

        // ==================== PESO SCHOOL ====================
        if (DB::table('users')->where('email', 'pesoschool@test.com')->doesntExist()) {
            DB::table('users')->insert([
                'user_type' => 'peso_school',
                'name' => 'PESO School Clark',
                'email' => 'pesoschool@test.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'telephone' => $faker->phoneNumber,
                'address' => 'Clark Education Zone, Pampanga',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✅ PESO School user created: pesoschool@test.com / password123');
        } else {
            $this->command->info('ℹ️ PESO School user already exists.');
        }

        // ==================== MANPOWER AGENCY ====================
        if (DB::table('users')->where('email', 'manpower@test.com')->doesntExist()) {
            DB::table('users')->insert([
                'user_type' => 'manpower_agency',
                'name' => 'Clark Manpower Agency',
                'email' => 'manpower@test.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'telephone' => $faker->phoneNumber,
                'address' => 'Clark Industrial Park, Pampanga',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✅ Manpower Agency user created: manpower@test.com / password123');
        } else {
            $this->command->info('ℹ️ Manpower Agency user already exists.');
        }
    }
}
