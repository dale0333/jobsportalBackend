<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LocatorImport;

class LocatorSeeder extends Seeder
{
    public function run()
    {
        Excel::import(
            new LocatorImport(),
            database_path('seeders/data/locators.xlsx')
        );
    }
}
