<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployerImport;


class LocatorSeeder extends Seeder
{
    public function run()
    {
        Excel::import(
            new EmployerImport(),
            database_path('seeders/data/employer.xlsx')
        );
    }
}
