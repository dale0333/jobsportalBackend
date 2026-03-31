<?php

namespace App\Imports;

use App\Models\PesoStudent;
use App\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentsImport implements ToCollection
{
    protected $user_id;

    public $processed = 0;
    public $success = 0;
    public $failed = 0;
    public $errors = [];

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            // Skip header + instruction row
            if ($index < 2) continue;

            $this->processed++;

            try {
                // Skip empty row
                if (!$row[0] && !$row[1] && !$row[2]) {
                    continue;
                }

                // Validate required fields
                if (!$row[0] || !$row[1] || !$row[2]) {
                    throw new \Exception("Missing required fields (Category, Name, Email)");
                }

                // Find category
                $category = Category::where('name', trim($row[0]))->first();

                if (!$category) {
                    throw new \Exception("Invalid category: {$row[0]}");
                }

                // Save to DB
                PesoStudent::create([
                    'user_id' => $this->user_id,
                    'type' => $category->id,
                    'name' => $row[1],
                    'email' => $row[2],
                    'gender' => $row[3] ?? null,
                    'contact' => $row[4] ?? null,
                    'education_level' => $row[5] ?? null,
                    'field_of_study' => $row[6] ?? null,
                    'skills' => $row[7] ?? null,
                ]);

                $this->success++;
            } catch (\Exception $e) {
                $this->failed++;
                $this->errors[] = [
                    'row' => $index + 1,
                    'message' => $e->getMessage()
                ];
            }
        }
    }
}
