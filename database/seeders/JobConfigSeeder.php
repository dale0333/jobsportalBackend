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

        $categories = [
            ['name' => 'IT & Software', 'description' => 'Jobs in information technology and software development'],
            ['name' => 'Finance & Accounting', 'description' => 'Roles in finance, accounting, and banking'],
            ['name' => 'Healthcare', 'description' => 'Medical and healthcare-related job opportunities'],
            ['name' => 'Education & Training', 'description' => 'Teaching and educational roles'],
            ['name' => 'Sales & Marketing', 'description' => 'Positions in sales, marketing, and business development'],
            ['name' => 'Customer Service', 'description' => 'Customer support and service-oriented jobs'],
            ['name' => 'Engineering', 'description' => 'Engineering roles across various disciplines'],
            ['name' => 'Human Resources', 'description' => 'HR management and recruitment positions'],
            ['name' => 'Design & Creative', 'description' => 'Creative roles in design and multimedia'],
            ['name' => 'Operations & Logistics', 'description' => 'Operations, supply chain and logistics positions'],
        ];

        $subCategories = [
            // IT & Software
            ['category' => 'IT & Software', 'name' => 'Software Development', 'description' => 'Jobs related to software engineering and development'],
            ['category' => 'IT & Software', 'name' => 'Web Development', 'description' => 'Frontend and backend web development roles'],
            ['category' => 'IT & Software', 'name' => 'Mobile Development', 'description' => 'iOS and Android app development positions'],
            ['category' => 'IT & Software', 'name' => 'DevOps', 'description' => 'DevOps engineering and infrastructure roles'],
            ['category' => 'IT & Software', 'name' => 'Network Administration', 'description' => 'Roles in managing and maintaining computer networks'],
            ['category' => 'IT & Software', 'name' => 'Cybersecurity', 'description' => 'Positions focused on protecting systems and data'],
            ['category' => 'IT & Software', 'name' => 'Data Science', 'description' => 'Jobs involving data analysis and machine learning'],
            ['category' => 'IT & Software', 'name' => 'Database Administration', 'description' => 'Database management and administration roles'],
            ['category' => 'IT & Software', 'name' => 'IT Support', 'description' => 'Technical support and helpdesk positions'],
            ['category' => 'IT & Software', 'name' => 'Cloud Computing', 'description' => 'AWS, Azure, and Google Cloud platform roles'],

            // Finance & Accounting
            ['category' => 'Finance & Accounting', 'name' => 'Accounting', 'description' => 'Roles in bookkeeping, auditing, and financial reporting'],
            ['category' => 'Finance & Accounting', 'name' => 'Financial Analysis', 'description' => 'Jobs analyzing financial data and trends'],
            ['category' => 'Finance & Accounting', 'name' => 'Investment Banking', 'description' => 'Positions in investment banking and asset management'],
            ['category' => 'Finance & Accounting', 'name' => 'Tax Accounting', 'description' => 'Tax preparation and planning specialists'],
            ['category' => 'Finance & Accounting', 'name' => 'Corporate Finance', 'description' => 'Financial management within corporations'],
            ['category' => 'Finance & Accounting', 'name' => 'Risk Management', 'description' => 'Financial risk assessment and mitigation'],
            ['category' => 'Finance & Accounting', 'name' => 'Wealth Management', 'description' => 'Personal financial advising and planning'],

            // Healthcare
            ['category' => 'Healthcare', 'name' => 'Nursing', 'description' => 'Nursing roles in hospitals and clinics'],
            ['category' => 'Healthcare', 'name' => 'Medical Research', 'description' => 'Jobs in clinical research and trials'],
            ['category' => 'Healthcare', 'name' => 'Pharmacy', 'description' => 'Roles in pharmaceutical care and drug dispensing'],
            ['category' => 'Healthcare', 'name' => 'Medical Practice', 'description' => 'Physicians and medical practitioners'],
            ['category' => 'Healthcare', 'name' => 'Healthcare Administration', 'description' => 'Hospital and clinic management roles'],
            ['category' => 'Healthcare', 'name' => 'Mental Health', 'description' => 'Psychologists, therapists, and counselors'],
            ['category' => 'Healthcare', 'name' => 'Dentistry', 'description' => 'Dental care professionals'],

            // Education & Training
            ['category' => 'Education & Training', 'name' => 'Teaching', 'description' => 'Teaching positions in schools and educational institutions'],
            ['category' => 'Education & Training', 'name' => 'Curriculum Development', 'description' => 'Jobs designing educational programs and materials'],
            ['category' => 'Education & Training', 'name' => 'Educational Administration', 'description' => 'School and university administration roles'],
            ['category' => 'Education & Training', 'name' => 'Tutoring', 'description' => 'Private tutoring and academic coaching'],
            ['category' => 'Education & Training', 'name' => 'Corporate Training', 'description' => 'Employee training and development'],
            ['category' => 'Education & Training', 'name' => 'Online Education', 'description' => 'E-learning and virtual teaching positions'],

            // Sales & Marketing
            ['category' => 'Sales & Marketing', 'name' => 'Digital Marketing', 'description' => 'Roles in online marketing and social media management'],
            ['category' => 'Sales & Marketing', 'name' => 'Sales Management', 'description' => 'Positions leading sales teams and strategies'],
            ['category' => 'Sales & Marketing', 'name' => 'Content Marketing', 'description' => 'Content creation and strategy roles'],
            ['category' => 'Sales & Marketing', 'name' => 'SEO/SEM', 'description' => 'Search engine optimization and marketing specialists'],
            ['category' => 'Sales & Marketing', 'name' => 'Brand Management', 'description' => 'Brand strategy and development positions'],
            ['category' => 'Sales & Marketing', 'name' => 'Market Research', 'description' => 'Consumer and market analysis roles'],

            // Customer Service
            ['category' => 'Customer Service', 'name' => 'Customer Support', 'description' => 'Roles assisting customers with inquiries and issues'],
            ['category' => 'Customer Service', 'name' => 'Call Center', 'description' => 'Call center and telephone support positions'],
            ['category' => 'Customer Service', 'name' => 'Technical Support', 'description' => 'Technical assistance and troubleshooting'],
            ['category' => 'Customer Service', 'name' => 'Client Success', 'description' => 'Customer success and account management'],
            ['category' => 'Customer Service', 'name' => 'Customer Experience', 'description' => 'CX strategy and improvement roles'],

            // Engineering
            ['category' => 'Engineering', 'name' => 'Civil Engineering', 'description' => 'Infrastructure and construction engineering'],
            ['category' => 'Engineering', 'name' => 'Mechanical Engineering', 'description' => 'Machine and mechanical systems design'],
            ['category' => 'Engineering', 'name' => 'Electrical Engineering', 'description' => 'Electrical systems and electronics engineering'],
            ['category' => 'Engineering', 'name' => 'Chemical Engineering', 'description' => 'Chemical process and production engineering'],
            ['category' => 'Engineering', 'name' => 'Industrial Engineering', 'description' => 'Process optimization and systems engineering'],
            ['category' => 'Engineering', 'name' => 'Aerospace Engineering', 'description' => 'Aircraft and spacecraft engineering roles'],

            // Human Resources
            ['category' => 'Human Resources', 'name' => 'Recruitment', 'description' => 'Talent acquisition and recruiting positions'],
            ['category' => 'Human Resources', 'name' => 'HR Management', 'description' => 'Human resources leadership roles'],
            ['category' => 'Human Resources', 'name' => 'Compensation & Benefits', 'description' => 'Salary and benefits administration'],
            ['category' => 'Human Resources', 'name' => 'Employee Relations', 'description' => 'Workplace relations and conflict resolution'],
            ['category' => 'Human Resources', 'name' => 'Training & Development', 'description' => 'Employee training and career development'],

            // Design & Creative
            ['category' => 'Design & Creative', 'name' => 'Graphic Design', 'description' => 'Visual design and branding roles'],
            ['category' => 'Design & Creative', 'name' => 'UI/UX Design', 'description' => 'User interface and experience design'],
            ['category' => 'Design & Creative', 'name' => 'Video Production', 'description' => 'Video editing and production positions'],
            ['category' => 'Design & Creative', 'name' => 'Photography', 'description' => 'Professional photography roles'],
            ['category' => 'Design & Creative', 'name' => 'Animation', 'description' => '2D and 3D animation positions'],

            // Operations & Logistics
            ['category' => 'Operations & Logistics', 'name' => 'Supply Chain Management', 'description' => 'Supply chain and logistics coordination'],
            ['category' => 'Operations & Logistics', 'name' => 'Warehouse Management', 'description' => 'Inventory and warehouse operations'],
            ['category' => 'Operations & Logistics', 'name' => 'Procurement', 'description' => 'Purchasing and vendor management'],
            ['category' => 'Operations & Logistics', 'name' => 'Project Management', 'description' => 'Project coordination and leadership'],
            ['category' => 'Operations & Logistics', 'name' => 'Quality Assurance', 'description' => 'Quality control and process improvement'],
        ];

        foreach ($categories as $category) {
            $categoryId = DB::table('categories')->insertGetId([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Insert sub-categories for this category
            foreach ($subCategories as $subCategory) {
                if ($subCategory['category'] === $category['name']) {
                    DB::table('sub_categories')->insert([
                        'category_id' => $categoryId,
                        'name' => $subCategory['name'],
                        'description' => $subCategory['description'],
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        // Define job config groups with icons and details
        $jobConfigs = [
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
            $jobConfigId = DB::table('attributes')->insertGetId([
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

                DB::table('sub_attributes')->insert([
                    'attribute_id' => $jobConfigId,
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
