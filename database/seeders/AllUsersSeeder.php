<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AllUsersSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();

        // Common data arrays
        $educationLevels = ['High School', 'Associate Degree', 'Bachelor\'s Degree', 'Master\'s Degree', 'PhD'];
        $fieldsOfStudy = ['Computer Science', 'Business Administration', 'Engineering', 'Marketing', 'Finance', 'Healthcare', 'Education', 'Arts', 'Psychology', 'Nursing'];
        $skills = ['PHP', 'JavaScript', 'Python', 'Java', 'React', 'Vue.js', 'Laravel', 'Node.js', 'MySQL', 'MongoDB', 'Project Management', 'Communication', 'Leadership', 'Problem Solving', 'Teamwork'];
        $locations = ['Manila', 'Quezon City', 'Cebu City', 'Davao City', 'Makati', 'Taguig', 'Pasig', 'Mandaluyong', 'Bacolod', 'Iloilo City'];
        $industries = ['Technology', 'Healthcare', 'Finance', 'Education', 'Manufacturing', 'Retail', 'Hospitality', 'Construction', 'Transportation', 'Real Estate'];
        $companySizes = ['1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'];
        $schoolTypes = ['Public High School', 'Private High School', 'State University', 'Private University', 'Technical School', 'Vocational School'];

        // ==================== JOB SEEKERS (20) ====================
        $jobSeekers = [];
        for ($i = 1; $i <= 20; $i++) {
            $dateOfBirth = $faker->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d');
            $gender = $faker->randomElement(['male', 'female', 'other']);
            $firstName = $gender === 'male' ? $faker->firstNameMale : ($gender === 'female' ? $faker->firstNameFemale : $faker->firstName);
            $lastName = $faker->lastName;
            $createdAt = $faker->dateTimeBetween('2025-01-01', '2025-12-31');

            // Create user
            $userId = DB::table('users')->insertGetId([
                'user_type' => 'job_seeker',
                'name' => $firstName . ' ' . $lastName,
                'email' => strtolower($firstName . '.' . $lastName . $i . '@jobseeker.com'),
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'company_name' => null,
                'contact_person' => null,
                'is_active' => $faker->boolean(90),
                'remember_token' => null,
                'created_at' => $createdAt,
                'updated_at' => $faker->dateTimeBetween($createdAt->format('Y-m-d'), '2025-12-31'),
            ]);

            $jobSeekers[] = [
                'user_id' => $userId,
                'date_of_birth' => $dateOfBirth,
                'gender' => $gender,
                'education_level' => $faker->randomElement($educationLevels),
                'field_of_study' => $faker->randomElement($fieldsOfStudy),
                'skills' => json_encode($faker->randomElements($skills, $faker->numberBetween(3, 8))),
                'years_of_experience' => $faker->numberBetween(0, 20),
                'current_location' => $faker->randomElement($locations),
                'preferred_location' => $faker->randomElement($locations),
                'expected_salary' => $faker->numberBetween(20000, 80000),
                'resume_file_path' => $faker->optional(0.7)->word . '.pdf',
                'bio' => $faker->paragraph(3),
                'is_available' => $faker->boolean(80),
                'created_at' => $createdAt,
                'updated_at' => $faker->dateTimeBetween($createdAt->format('Y-m-d'), '2025-12-31'),
            ];
        }
        DB::table('job_seekers')->insert($jobSeekers);

        // ==================== EMPLOYERS (20) ====================
        $employers = [];
        for ($i = 1; $i <= 20; $i++) {
            $companyName = $faker->company;
            $createdAt = $faker->dateTimeBetween('2025-01-01', '2025-12-31');

            // Create user
            $userId = DB::table('users')->insertGetId([
                'user_type' => 'employer',
                'name' => $faker->name,
                'email' => strtolower(str_replace(' ', '', $companyName) . $i . '@employer.com'),
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'company_name' => $companyName,
                'contact_person' => $faker->name,
                'is_active' => $faker->boolean(90),
                'remember_token' => null,
                'created_at' => $createdAt,
                'updated_at' => $faker->dateTimeBetween($createdAt->format('Y-m-d'), '2025-12-31'),
            ]);

            $employers[] = [
                'user_id' => $userId,
                'company_size' => $faker->randomElement($companySizes),
                'industry' => $faker->randomElement($industries),
                'company_address' => $faker->address,
                'website' => $faker->optional(0.8)->url,
                'company_description' => $faker->paragraph(4),
                'is_verified' => $faker->boolean(70),
                'created_at' => $createdAt,
                'updated_at' => $faker->dateTimeBetween($createdAt->format('Y-m-d'), '2025-12-31'),
            ];
        }
        DB::table('employers')->insert($employers);

        // ==================== PESO SCHOOLS (20) ====================
        $pesoSchools = [];
        for ($i = 1; $i <= 20; $i++) {
            $schoolName = $faker->randomElement(['University of', 'College of', 'Polytechnic University of', 'Technical Institute of']) . ' ' . $faker->city;
            $createdAt = $faker->dateTimeBetween('2025-01-01', '2025-12-31');

            // Create user
            $userId = DB::table('users')->insertGetId([
                'user_type' => 'peso_school',
                'name' => $schoolName,
                'email' => strtolower(str_replace(' ', '', $schoolName) . $i . '@school.edu.ph'),
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'company_name' => $schoolName,
                'contact_person' => $faker->name,
                'is_active' => $faker->boolean(90),
                'remember_token' => null,
                'created_at' => $createdAt,
                'updated_at' => $faker->dateTimeBetween($createdAt->format('Y-m-d'), '2025-12-31'),
            ]);

            // FIXED: Ensure we don't request more elements than available
            $numCourses = $faker->numberBetween(3, min(8, count($fieldsOfStudy))); // Max 8 or available fields
            $pesoSchools[] = [
                'user_id' => $userId,
                'school_type' => $faker->randomElement($schoolTypes),
                'accreditation_status' => $faker->randomElement(['Accredited', 'Pending', 'Fully Accredited']),
                'total_students' => $faker->numberBetween(500, 10000),
                'courses_offered' => json_encode($faker->randomElements($fieldsOfStudy, $numCourses)),
                'school_address' => $faker->address,
                'website' => $faker->optional(0.9)->url,
                'description' => $faker->paragraph(4),
                'is_verified' => $faker->boolean(80),
                'created_at' => $createdAt,
                'updated_at' => $faker->dateTimeBetween($createdAt->format('Y-m-d'), '2025-12-31'),
            ];
        }
        DB::table('peso_schools')->insert($pesoSchools);

        // ==================== MANPOWER AGENCIES (20) ====================
        $manpowerAgencies = [];
        for ($i = 1; $i <= 20; $i++) {
            $agencyName = $faker->company . ' Manpower Agency';
            $createdAt = $faker->dateTimeBetween('2025-01-01', '2025-12-31');

            // Generate email with max 20 characters for local part
            $localPart = strtolower(str_replace(' ', '', $agencyName) . $i);
            $maxLocalLength = 20; // Max characters before @ symbol
            $localPart = substr($localPart, 0, $maxLocalLength);

            // Create user
            $userId = DB::table('users')->insertGetId([
                'user_type' => 'manpower_agency',
                'name' => $agencyName,
                'email' => $localPart . '@manpower.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'company_name' => $agencyName,
                'contact_person' => $faker->name,
                'is_active' => $faker->boolean(90),
                'remember_token' => null,
                'created_at' => $createdAt,
                'updated_at' => $faker->dateTimeBetween($createdAt->format('Y-m-d'), '2025-12-31'),
            ]);

            $manpowerAgencies[] = [
                'user_id' => $userId,
                'license_number' => 'LIC-' . $faker->randomNumber(6),
                'services_offered' => json_encode(['Local Employment', 'Overseas Employment', 'Skills Training', 'Career Counseling']),
                'years_in_operation' => $faker->numberBetween(1, 30),
                'agency_address' => $faker->address,
                'website' => $faker->optional(0.7)->url,
                'description' => $faker->paragraph(4),
                'is_verified' => $faker->boolean(60),
                'created_at' => $createdAt,
                'updated_at' => $faker->dateTimeBetween($createdAt->format('Y-m-d'), '2025-12-31'),
            ];
        }
        DB::table('manpower_agencies')->insert($manpowerAgencies);

        DB::table('email_smtps')->insert(
            [
                'host' => 'smtp.gmail.com',
                'port' => '587',
                'email' => 'noreply@nyc.gov.ph',
                'password' => 'jcvpjbvoolfruktj',
                'encryption' => 'tls',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $this->command->info('Successfully seeded 20 job seekers, 20 employers, 20 PESO schools, and 20 manpower agencies!');
    }
}
