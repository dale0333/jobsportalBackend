<?php

namespace App\Imports;

use App\Models\ReferenceDetail;
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

    private $referenceId;
    private $rowCount = 0;
    private $successCount = 0;
    private $user;

    public function __construct($user, $referenceId)
    {
        $this->user = $user;
        $this->referenceId = $referenceId;
    }

    public function model(array $row)
    {
        $this->rowCount++;

        if ($this->user->user_type === 'employer') {
            $companyName = $this->user->name;
        } else {
            $companyName = $row['company_name'] ?? null;
        }

        $this->successCount++;

        return new ReferenceDetail([
            'reference_id' => $this->referenceId,
            'company' => $companyName,
            'name' => $row['name'] ?? null,
            'category' => $row['category'] ?? null,
            'position' => $row['position'] ?? null,
            'nationality' => $row['nationality'] ?? null,
            'gender' => $row['gender'] ?? null,
            'domicile' => $row['domicile'] ?? null,
            'status' => $row['status'] ?? null,

            'tem_res_add'  => $row['temporary_residence_address'] ?? null,
            'tem_province' => $row['tem_province'] ?? null,
            'tem_mun_brgy' => $row['tem_municipality_barangay'] ?? null,
            'per_res_add'  => $row['permanent_residence_address'] ?? null,
            'per_province' => $row['per_province'] ?? null,
            'per_mun_brgy' => $row['per_municipality_barangay'] ?? null,
        ]);
    }

    public function rules(): array
    {
        // Rules MUST match Excel column headers, not database fields
        return [
            'company' => 'nullable|max:255',
            'name' => 'required|max:255',
            'category' => 'required|max:255',
            'position' => 'required|max:255',
            'nationality' => 'required|max:255',
            'gender' => 'required|in:Male,Female',
            'domicile' => 'required|max:255',
            'status' => 'required|max:255',

            // Excel column names:
            'temporary_residence_address' => 'required|max:255',
            'tem_province' => 'required|max:255',
            'tem_municipality_barangay' => 'required|max:255',
            'permanent_residence_address' => 'required|max:255',
            'per_province' => 'required|max:255',
            'per_municipality_barangay' => 'required|max:255',
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
