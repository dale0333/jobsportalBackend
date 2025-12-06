<?php

namespace App\Imports;

use App\Models\Reference;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ReferencesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    private $userId;
    private $rowCount = 0;
    private $successCount = 0;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function model(array $row)
    {
        $this->rowCount++;

        // If row failed validation, it won't reach here
        $this->successCount++;

        return new Reference([
            'user_id' => $this->userId,
            'name' => $row['name'] ?? null,
            'category' => $row['category'] ?? null,
            'position' => $row['position'] ?? null,
            'nationality' => $row['nationality'] ?? null,
            'gender' => $row['gender'] ?? null,
            'domicile' => $row['domicile'] ?? null,
            'status' => $row['status'] ?? null,

            // Match Excel column headers exactly
            'tem_res_add'   => $row['temporary_residence_address'] ?? null,
            'tem_province'  => $row['tem_province'] ?? null,
            'tem_mun_brgy'  => $row['tem_municipality_barangay'] ?? null,
            'per_res_add'   => $row['permanent_residence_address'] ?? null,
            'per_province'  => $row['per_province'] ?? null,
            'per_mun_brgy'  => $row['per_municipality_barangay'] ?? null,
        ]);
    }

    public function rules(): array
    {
        // Rules MUST match Excel column headers, not database fields
        return [
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'domicile' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',

            // Excel column names:
            'temporary_residence_address' => 'nullable|string|max:255',
            'tem_province' => 'nullable|string|max:255',
            'tem_municipality_barangay' => 'nullable|string|max:255',
            'permanent_residence_address' => 'nullable|string|max:255',
            'per_province' => 'nullable|string|max:255',
            'per_municipality_barangay' => 'nullable|string|max:255',
        ];
    }

    public function customValidationMessages()
    {
        return [];
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount - count($this->failures());
    }

    public function getFailureCount(): int
    {
        return count($this->failures());
    }
}
