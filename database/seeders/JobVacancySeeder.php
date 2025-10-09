<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\{JobConfig, JobConfigDetail, Category, SubCategory};

class JobVacancySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Get a user ID to associate with the jobs
        $userId = 1; // Change this to a valid user ID from your users table

        // Get all categories with their sub-categories
        $categories = Category::with('subCategories')->get();

        // Get job config details
        $locations = JobConfigDetail::whereHas('jobConfig', function ($query) {
            $query->where('slug', 'locations');
        })->get();

        $types = JobConfigDetail::whereHas('jobConfig', function ($query) {
            $query->where('slug', 'types');
        })->get();

        $qualifications = JobConfigDetail::whereHas('jobConfig', function ($query) {
            $query->where('slug', 'qualifications');
        })->get();

        $levels = JobConfigDetail::whereHas('jobConfig', function ($query) {
            $query->where('slug', 'levels');
        })->get();

        $jobVacancies = [
            [
                'title' => 'Senior Full Stack Developer',
                'content' => 'We are looking for an experienced Full Stack Developer to join our dynamic team. You will be responsible for developing and maintaining web applications using modern technologies.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '5+',
                'salary' => '₱90,000 - ₱120,000',
                'deadline' => $now->copy()->addDays(30),
            ],
            [
                'title' => 'Cloud Infrastructure Engineer',
                'content' => 'Join our cloud team to design, implement and maintain cloud infrastructure solutions. Experience with AWS, Azure, or GCP required.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '3',
                'salary' => '₱85,000 - ₱110,000',
                'deadline' => $now->copy()->addDays(25),
            ],
            [
                'title' => 'Mobile App Developer (React Native)',
                'content' => 'Develop cutting-edge mobile applications for iOS and Android using React Native. Work closely with design and product teams.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '2',
                'salary' => '₱75,000 - ₱95,000',
                'deadline' => $now->copy()->addDays(20),
            ],
            [
                'title' => 'Frontend Developer - Vue.js',
                'content' => 'Looking for a Vue.js developer to build responsive and interactive user interfaces. Experience with modern frontend tools required.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '1',
                'salary' => '₱60,000 - ₱80,000',
                'deadline' => $now->copy()->addDays(15),
            ],
            [
                'title' => 'DevOps Engineer',
                'content' => 'Implement and maintain CI/CD pipelines, automate deployment processes, and ensure system reliability. Docker and Kubernetes experience preferred.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '4',
                'salary' => '₱95,000 - ₱125,000',
                'deadline' => $now->copy()->addDays(35),
            ],
            [
                'title' => 'Backend Developer - Node.js',
                'content' => 'Build scalable backend services and APIs using Node.js. Experience with databases and microservices architecture required.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '3',
                'salary' => '₱80,000 - ₱100,000',
                'deadline' => $now->copy()->addDays(28),
            ],
            [
                'title' => 'Data Scientist',
                'content' => 'Analyze complex datasets and build machine learning models to drive business insights and decisions.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '4',
                'salary' => '₱100,000 - ₱130,000',
                'deadline' => $now->copy()->addDays(40),
            ],
            [
                'title' => 'UI/UX Designer',
                'content' => 'Create intuitive and engaging user experiences for our digital products. Proficiency in design tools and user research required.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '3',
                'salary' => '₱70,000 - ₱90,000',
                'deadline' => $now->copy()->addDays(22),
            ],
            [
                'title' => 'Cybersecurity Analyst',
                'content' => 'Protect our systems and data from cyber threats. Monitor security infrastructure and respond to incidents.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '4',
                'salary' => '₱95,000 - ₱120,000',
                'deadline' => $now->copy()->addDays(32),
            ],
            [
                'title' => 'Project Manager',
                'content' => 'Lead software development projects from conception to delivery. Coordinate teams and ensure project success.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '5+',
                'salary' => '₱110,000 - ₱140,000',
                'deadline' => $now->copy()->addDays(45),
            ],
            [
                'title' => 'Quality Assurance Engineer',
                'content' => 'Ensure software quality through comprehensive testing strategies. Develop test plans and automate testing processes.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '2',
                'salary' => '₱65,000 - ₱85,000',
                'deadline' => $now->copy()->addDays(18),
            ],
            [
                'title' => 'Systems Administrator',
                'content' => 'Maintain and optimize our IT infrastructure. Ensure system reliability and performance.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '3',
                'salary' => '₱75,000 - ₱95,000',
                'deadline' => $now->copy()->addDays(25),
            ],
            [
                'title' => 'Technical Support Specialist',
                'content' => 'Provide technical assistance to customers and resolve software and hardware issues.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '1',
                'salary' => '₱35,000 - ₱45,000',
                'deadline' => $now->copy()->addDays(12),
            ],
            [
                'title' => 'Business Analyst',
                'content' => 'Bridge the gap between IT and business stakeholders. Analyze requirements and propose technical solutions.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '3',
                'salary' => '₱80,000 - ₱100,000',
                'deadline' => $now->copy()->addDays(30),
            ],
            [
                'title' => 'Database Administrator',
                'content' => 'Manage and maintain database systems. Ensure data integrity, security, and performance.',
                'category_id' => $categories->random()->id,
                'sub_category_ids' => function () use ($categories) {
                    $randomCategory = $categories->random();
                    return $randomCategory->subCategories->random(min(2, $randomCategory->subCategories->count()))->pluck('id')->toArray();
                },
                'job_location' => $locations->random()->id,
                'job_type' => $types->random()->id,
                'job_qualify' => $qualifications->random()->id,
                'job_level' => $levels->random()->id,
                'job_experience' => '4',
                'salary' => '₱90,000 - ₱115,000',
                'deadline' => $now->copy()->addDays(35),
            ],
        ];

        foreach ($jobVacancies as $job) {
            // Execute the closure to get sub_category_ids
            $subCategoryIds = is_callable($job['sub_category_ids']) ? $job['sub_category_ids']() : $job['sub_category_ids'];

            DB::table('job_vacancies')->insert([
                'employer_id' => $userId,
                'title' => $job['title'],
                'content' => $job['content'],
                'code' => uniqid(),
                'job_category' => $job['category_id'],
                'job_sub_category' => json_encode($subCategoryIds),
                'job_location' => $job['job_location'],
                'job_type' => $job['job_type'],
                'job_qualify' => $job['job_qualify'],
                'job_level' => $job['job_level'],
                'job_experience' => $job['job_experience'],
                'salary' => $job['salary'],
                'deadline' => $job['deadline'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
