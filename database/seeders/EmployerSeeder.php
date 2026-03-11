<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class EmployerSeeder extends Seeder
{
    public function run()
    {
        $files = [
            database_path('seeders/data/users.sql'),
            database_path('seeders/data/employers.sql'),
            database_path('seeders/data/references.sql'),
            database_path('seeders/data/reference_details.sql'),
        ];

        foreach ($files as $file) {
            if (File::exists($file)) {
                DB::unprepared(File::get($file));
            }
        }
    }
}
