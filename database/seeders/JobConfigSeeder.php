<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JobConfigSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Define job config groups with icons and details
        $jobConfigs = [
            [
                'name' => 'Services',
                'icon' => 'ri-apps-2-line',
                'description' => 'Professional services and industries',
                'details' => [
                    // Technical Services
                    ['name' => 'Development', 'description' => 'Software development, web development, and programming services'],
                    ['name' => 'Cloud Management', 'description' => 'Cloud infrastructure, deployment, and management services'],
                    ['name' => 'Mobile Application Development', 'description' => 'iOS, Android, and cross-platform mobile app development'],
                    ['name' => 'Web Development', 'description' => 'Frontend and backend web application development'],
                    ['name' => 'DevOps Services', 'description' => 'CI/CD, infrastructure automation, and deployment services'],

                    // Business Services
                    ['name' => 'Information Technology', 'description' => 'IT-related jobs including software development, networking, and tech support'],
                    ['name' => 'Finance Services', 'description' => 'Finance and accounting roles including banking, investment, and financial analysis'],
                    ['name' => 'Human Resources', 'description' => 'HR roles including recruitment, training, and employee relations'],
                    ['name' => 'Marketing Services', 'description' => 'Marketing and sales roles including digital marketing, advertising, and business development'],
                    ['name' => 'Healthcare Services', 'description' => 'Medical and healthcare professions including doctors, nurses, and medical staff'],
                    ['name' => 'Education Services', 'description' => 'Teaching and educational roles including teachers, professors, and trainers'],
                ],
            ],
            [
                'name' => 'Types',
                'icon' => 'ri-briefcase-2-line',
                'description' => 'Employment types and work arrangements',
                'details' => [
                    ['name' => 'Full-Time', 'description' => 'Regular full-time work with standard working hours and benefits'],
                    ['name' => 'Part-Time', 'description' => 'Less than 40 hours/week with flexible scheduling'],
                    ['name' => 'Internship', 'description' => 'Training roles for students and recent graduates'],
                    ['name' => 'Contract', 'description' => 'Fixed-term jobs with specific project durations'],
                    ['name' => 'Temporary', 'description' => 'Short-term employment for seasonal or project-based work'],
                    ['name' => 'Remote Work', 'description' => 'Work from anywhere with virtual collaboration'],
                ],
            ],
            [
                'name' => 'Skills',
                'icon' => 'ri-code-line',
                'description' => 'Technical and soft skills required for jobs',
                'details' => [
                    ['name' => 'PHP', 'description' => 'Backend development with PHP frameworks like Laravel'],
                    ['name' => 'React', 'description' => 'Frontend framework for building user interfaces'],
                    ['name' => 'Communication', 'description' => 'Effective verbal and written communication skills'],
                    ['name' => 'Leadership', 'description' => 'Team management and leadership capabilities'],
                    ['name' => 'Problem Solving', 'description' => 'Analytical thinking and creative problem-solving abilities'],
                    ['name' => 'Project Management', 'description' => 'Planning, executing, and managing projects effectively'],
                ],
            ],
            [
                'name' => 'Levels',
                'icon' => 'ri-lightbulb-line',
                'description' => 'Experience levels and seniority',
                'details' => [
                    ['name' => 'Entry Level', 'description' => 'Beginner positions for recent graduates or career changers'],
                    ['name' => 'Mid Level', 'description' => 'Experienced professionals with 2-5 years of relevant experience'],
                    ['name' => 'Senior Level', 'description' => 'Senior staff with 5+ years of expertise and leadership'],
                    ['name' => 'Manager', 'description' => 'Management roles with team leadership responsibilities'],
                    ['name' => 'Director', 'description' => 'Executive leadership with departmental oversight'],
                    ['name' => 'Executive', 'description' => 'C-level positions with organizational leadership'],
                ],
            ],
            [
                'name' => 'Locations',
                'icon' => 'ri-map-pin-line',
                'description' => 'Work location types and arrangements',
                'details' => [
                    ['name' => 'Main Office', 'description' => 'Company headquarters and primary work location'],
                    ['name' => 'Branch Office', 'description' => 'Regional branches and satellite offices'],
                    ['name' => 'Remote Location', 'description' => 'Work from home or any remote location'],
                    ['name' => 'Hybrid', 'description' => 'Combination of office and remote work'],
                    ['name' => 'On-site', 'description' => 'Work at client locations or specific project sites'],
                ],
            ],
            [
                'name' => 'Benefits',
                'icon' => 'ri-gift-line',
                'description' => 'Employee benefits and perks',
                'details' => [
                    ['name' => 'Health Insurance', 'description' => 'Comprehensive medical, dental, and vision insurance coverage'],
                    ['name' => 'Gym Membership', 'description' => 'Fitness perks and wellness program benefits'],
                    ['name' => 'Transportation Allowance', 'description' => 'Daily transport support and commuting assistance'],
                    ['name' => 'Meal Allowance', 'description' => 'Food and meal subsidies for employees'],
                    ['name' => 'Performance Bonus', 'description' => 'Incentive bonuses based on performance metrics'],
                    ['name' => 'Paid Time Off', 'description' => 'Vacation days, sick leave, and personal time'],
                ],
            ],
            [
                'name' => 'Qualifications',
                'icon' => 'ri-graduation-cap-line',
                'description' => 'Educational qualifications and certifications',
                'details' => [
                    ['name' => 'High School Diploma', 'description' => 'Secondary education completion certificate'],
                    ['name' => 'Associate Degree', 'description' => 'Two-year college degree or equivalent'],
                    ['name' => 'Bachelor Degree', 'description' => 'Four-year undergraduate university degree'],
                    ['name' => 'Master Degree', 'description' => 'Postgraduate degree for advanced specialization'],
                    ['name' => 'Doctorate PhD', 'description' => 'Highest academic degree for research and academia'],
                    ['name' => 'Professional Certification', 'description' => 'Industry-specific certifications and licenses'],
                    ['name' => 'Vocational Diploma', 'description' => 'Vocational or technical training certification'],
                    ['name' => 'No Formal Education', 'description' => 'Roles that prioritize skills over formal education'],
                ],
            ],
            [
                'name' => 'Languages',
                'icon' => 'ri-global-line',
                'description' => 'Language proficiency requirements',
                'details' => [
                    ['name' => 'English Language', 'description' => 'English language proficiency for business communication'],
                    ['name' => 'Spanish Language', 'description' => 'Spanish language skills for multilingual roles'],
                    ['name' => 'French Language', 'description' => 'French language proficiency for international business'],
                    ['name' => 'German Language', 'description' => 'German language skills for European markets'],
                    ['name' => 'Chinese Language', 'description' => 'Mandarin or Cantonese for Asian market roles'],
                    ['name' => 'Japanese Language', 'description' => 'Japanese language proficiency for business in Japan'],
                ],
            ],
            [
                'name' => 'Industries',
                'icon' => 'ri-building-line',
                'description' => 'Specific industry sectors and domains',
                'details' => [
                    ['name' => 'Technology Industry', 'description' => 'Software, hardware, and IT services industry'],
                    ['name' => 'Healthcare Industry', 'description' => 'Medical services, pharmaceuticals, and healthcare providers'],
                    ['name' => 'Finance Banking', 'description' => 'Banking, investment, and financial services sector'],
                    ['name' => 'Retail Industry', 'description' => 'Consumer goods, e-commerce, and retail operations'],
                    ['name' => 'Manufacturing Industry', 'description' => 'Production, assembly, and industrial manufacturing'],
                    ['name' => 'Hospitality Industry', 'description' => 'Hotels, restaurants, and tourism services'],
                ],
            ],
        ];

        foreach ($jobConfigs as $config) {
            // Insert parent job config
            $jobConfigId = DB::table('job_configs')->insertGetId([
                'name' => $config['name'],
                'slug' => Str::slug($config['name']),
                'icon' => $config['icon'],
                'description' => $config['description'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Insert related details
            foreach ($config['details'] as $detail) {
                // Create unique slug by combining category and detail name
                $uniqueSlug = Str::slug($config['name'] . ' ' . $detail['name']);

                DB::table('job_config_details')->insert([
                    'job_config_id' => $jobConfigId,
                    'name' => $detail['name'],
                    'slug' => $uniqueSlug,
                    'description' => $detail['description'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
