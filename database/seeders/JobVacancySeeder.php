<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JobVacancySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Get existing job config details IDs
        $services = DB::table('job_config_details')
            ->whereIn('name', ['Development', 'Cloud Management', 'Mobile Application Development', 'Web Development', 'DevOps Services'])
            ->pluck('id', 'name')
            ->toArray();

        $locations = DB::table('job_config_details')
            ->whereIn('name', ['Main Office', 'Branch Office', 'Remote Location', 'Hybrid'])
            ->pluck('id', 'name')
            ->toArray();

        $types = DB::table('job_config_details')
            ->whereIn('name', ['Full-Time', 'Part-Time', 'Remote Work', 'Contract', 'Internship'])
            ->pluck('id', 'name')
            ->toArray();

        $qualifications = DB::table('job_config_details')
            ->whereIn('name', ['Bachelor Degree', 'Master Degree', 'High School Diploma', 'Professional Certification'])
            ->pluck('id', 'name')
            ->toArray();

        $levels = DB::table('job_config_details')
            ->whereIn('name', ['Entry Level', 'Mid Level', 'Senior Level', 'Manager'])
            ->pluck('id', 'name')
            ->toArray();

        // Get a user ID to associate with the jobs
        $userId = DB::table('users')->first()->id;

        $jobVacancies = [
            [
                'title' => 'Senior Full Stack Developer',
                'content' => 'We are looking for an experienced Full Stack Developer to join our dynamic team. You will be responsible for developing and maintaining web applications using modern technologies.',
                'job_service' => $services['Development'],
                'job_location' => $locations['Remote Location'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Senior Level'],
                'job_experince' => '5+',
                'salary' => '$90,000 - $120,000',
                'deadline' => $now->copy()->addDays(30),
            ],
            [
                'title' => 'Cloud Infrastructure Engineer',
                'content' => 'Join our cloud team to design, implement and maintain cloud infrastructure solutions. Experience with AWS, Azure, or GCP required.',
                'job_service' => $services['Cloud Management'],
                'job_location' => $locations['Hybrid'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Mid Level'],
                'job_experince' => '3',
                'salary' => '$85,000 - $110,000',
                'deadline' => $now->copy()->addDays(25),
            ],
            [
                'title' => 'Mobile App Developer (React Native)',
                'content' => 'Develop cutting-edge mobile applications for iOS and Android using React Native. Work closely with design and product teams.',
                'job_service' => $services['Mobile Application Development'],
                'job_location' => $locations['Remote Location'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Mid Level'],
                'job_experince' => '2',
                'salary' => '$75,000 - $95,000',
                'deadline' => $now->copy()->addDays(20),
            ],
            [
                'title' => 'Frontend Developer - Vue.js',
                'content' => 'Looking for a Vue.js developer to build responsive and interactive user interfaces. Experience with modern frontend tools required.',
                'job_service' => $services['Web Development'],
                'job_location' => $locations['Main Office'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Entry Level'],
                'job_experince' => '1',
                'salary' => '$60,000 - $80,000',
                'deadline' => $now->copy()->addDays(15),
            ],
            [
                'title' => 'DevOps Engineer',
                'content' => 'Implement and maintain CI/CD pipelines, automate deployment processes, and ensure system reliability. Docker and Kubernetes experience preferred.',
                'job_service' => $services['DevOps Services'],
                'job_location' => $locations['Remote Location'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Senior Level'],
                'job_experince' => '4',
                'salary' => '$95,000 - $125,000',
                'deadline' => $now->copy()->addDays(35),
            ],
            [
                'title' => 'Backend Developer - Node.js',
                'content' => 'Build scalable backend services and APIs using Node.js. Experience with databases and microservices architecture required.',
                'job_service' => $services['Development'],
                'job_location' => $locations['Hybrid'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Mid Level'],
                'job_experince' => '3',
                'salary' => '$80,000 - $100,000',
                'deadline' => $now->copy()->addDays(28),
            ],
            [
                'title' => 'Cloud Security Specialist',
                'content' => 'Ensure cloud infrastructure security and compliance. Implement security best practices and conduct regular audits.',
                'job_service' => $services['Cloud Management'],
                'job_location' => $locations['Main Office'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Professional Certification'],
                'job_level' => $levels['Senior Level'],
                'job_experince' => '5+',
                'salary' => '$100,000 - $130,000',
                'deadline' => $now->copy()->addDays(40),
            ],
            [
                'title' => 'iOS Developer',
                'content' => 'Create innovative iOS applications using Swift and SwiftUI. Collaborate with cross-functional teams to define and implement new features.',
                'job_service' => $services['Mobile Application Development'],
                'job_location' => $locations['Remote Location'],
                'job_type' => $types['Contract'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Mid Level'],
                'job_experince' => '3',
                'salary' => '$85,000 - $105,000',
                'deadline' => $now->copy()->addDays(22),
            ],
            [
                'title' => 'Full Stack Developer Intern',
                'content' => 'Great opportunity for students to learn full stack development. Mentorship provided and potential for full-time employment.',
                'job_service' => $services['Development'],
                'job_location' => $locations['Branch Office'],
                'job_type' => $types['Internship'],
                'job_qualify' => $qualifications['High School Diploma'],
                'job_level' => $levels['Entry Level'],
                'job_experince' => '0',
                'salary' => '$25 - $30 per hour',
                'deadline' => $now->copy()->addDays(10),
            ],
            [
                'title' => 'AWS Solutions Architect',
                'content' => 'Design and implement AWS cloud solutions for enterprise clients. AWS certification preferred.',
                'job_service' => $services['Cloud Management'],
                'job_location' => $locations['Remote Location'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Senior Level'],
                'job_experince' => '5+',
                'salary' => '$110,000 - $140,000',
                'deadline' => $now->copy()->addDays(45),
            ],
            [
                'title' => 'Android Developer',
                'content' => 'Develop native Android applications using Kotlin and Java. Work on exciting projects with modern Android architecture.',
                'job_service' => $services['Mobile Application Development'],
                'job_location' => $locations['Hybrid'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Mid Level'],
                'job_experince' => '2',
                'salary' => '$75,000 - $95,000',
                'deadline' => $now->copy()->addDays(18),
            ],
            [
                'title' => 'Python Django Developer',
                'content' => 'Build robust web applications using Django framework. Experience with REST APIs and database design required.',
                'job_service' => $services['Web Development'],
                'job_location' => $locations['Main Office'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Mid Level'],
                'job_experince' => '3',
                'salary' => '$80,000 - $100,000',
                'deadline' => $now->copy()->addDays(25),
            ],
            [
                'title' => 'Site Reliability Engineer',
                'content' => 'Ensure high availability and performance of our systems. Implement monitoring and alerting solutions.',
                'job_service' => $services['DevOps Services'],
                'job_location' => $locations['Remote Location'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Senior Level'],
                'job_experince' => '4',
                'salary' => '$95,000 - $120,000',
                'deadline' => $now->copy()->addDays(30),
            ],
            [
                'title' => 'Part-time Web Developer',
                'content' => 'Flexible part-time position for web development projects. Perfect for students or those seeking work-life balance.',
                'job_service' => $services['Web Development'],
                'job_location' => $locations['Remote Location'],
                'job_type' => $types['Part-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Entry Level'],
                'job_experince' => '1',
                'salary' => '$35 - $45 per hour',
                'deadline' => $now->copy()->addDays(12),
            ],
            [
                'title' => 'Azure Cloud Engineer',
                'content' => 'Manage and optimize Azure cloud infrastructure. Experience with Azure services and PowerShell required.',
                'job_service' => $services['Cloud Management'],
                'job_location' => $locations['Hybrid'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Mid Level'],
                'job_experince' => '3',
                'salary' => '$85,000 - $110,000',
                'deadline' => $now->copy()->addDays(28),
            ],
            [
                'title' => 'React.js Developer',
                'content' => 'Build modern user interfaces using React.js. Experience with state management and component libraries preferred.',
                'job_service' => $services['Web Development'],
                'job_location' => $locations['Remote Location'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Mid Level'],
                'job_experince' => '2',
                'salary' => '$75,000 - $95,000',
                'deadline' => $now->copy()->addDays(20),
            ],
            [
                'title' => 'Senior Mobile Architect',
                'content' => 'Lead mobile architecture decisions and mentor junior developers. Extensive experience with mobile development required.',
                'job_service' => $services['Mobile Application Development'],
                'job_location' => $locations['Main Office'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Master Degree'],
                'job_level' => $levels['Senior Level'],
                'job_experince' => '6-10',
                'salary' => '$120,000 - $150,000',
                'deadline' => $now->copy()->addDays(50),
            ],
            [
                'title' => 'Junior DevOps Engineer',
                'content' => 'Entry-level position in DevOps. Training provided on CI/CD, containerization, and cloud platforms.',
                'job_service' => $services['DevOps Services'],
                'job_location' => $locations['Branch Office'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Entry Level'],
                'job_experince' => '0',
                'salary' => '$60,000 - $75,000',
                'deadline' => $now->copy()->addDays(15),
            ],
            [
                'title' => 'Cloud Database Administrator',
                'content' => 'Manage cloud databases and ensure data integrity. Experience with SQL and NoSQL databases required.',
                'job_service' => $services['Cloud Management'],
                'job_location' => $locations['Remote Location'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Mid Level'],
                'job_experince' => '4',
                'salary' => '$90,000 - $115,000',
                'deadline' => $now->copy()->addDays(32),
            ],
            [
                'title' => 'Full Stack Developer - MEAN Stack',
                'content' => 'Develop end-to-end solutions using MongoDB, Express.js, Angular, and Node.js. Full stack development position.',
                'job_service' => $services['Development'],
                'job_location' => $locations['Hybrid'],
                'job_type' => $types['Full-Time'],
                'job_qualify' => $qualifications['Bachelor Degree'],
                'job_level' => $levels['Mid Level'],
                'job_experince' => '3',
                'salary' => '$85,000 - $105,000',
                'deadline' => $now->copy()->addDays(26),
            ],
        ];

        foreach ($jobVacancies as $job) {
            DB::table('job_vacancies')->insert([
                'user_id' => $userId,
                'title' => $job['title'],
                'content' => $job['content'],
                'code' => 'JOB' . Str::upper(Str::random(6)),

                'job_service' => $job['job_service'],
                'job_location' => $job['job_location'],
                'job_type' => $job['job_type'],
                'job_qualify' => $job['job_qualify'],
                'job_level' => $job['job_level'],

                'job_experince' => $job['job_experince'],
                'salary' => $job['salary'],
                'deadline' => $job['deadline'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
