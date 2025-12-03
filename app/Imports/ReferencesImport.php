<?php

namespace App\Imports;

use App\Models\Reference;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ReferencesImport implements ToModel, WithValidation, SkipsOnError, SkipsOnFailure
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

        if ($this->rowCount === 1) {
            return null;
        }

        $this->successCount++;

        return new Reference([
            'user_id'              => $this->userId,
            'name'                 => $row[0] ?? null,
            'category'             => $row[1] ?? null,
            'position'             => $row[2] ?? null,
            'nationality'          => $row[3] ?? null,
            'gender'               => $row[4] ?? null,
            'domicile'             => $row[5] ?? null,
            'status'               => $row[6] ?? null,
            'tem_res_add'          => $row[7] ?? null,
            'tem_province'         => $row[8] ?? null,
            'tem_mun_brgy'         => $row[9] ?? null,
            'per_res_add'          => $row[10] ?? null,
            'per_province'         => $row[11] ?? null,
            'per_mun_brgy'         => $row[12] ?? null,
        ]);
    }


    public function rules(): array
    {
        return [
            '0'  => 'required|string|max:255',        // A = name
            '1'  => 'nullable|string|max:255',        // B = category
            '2'  => 'nullable|string|max:255',        // C = position
            '3'  => 'nullable|string|max:255',        // D = nationality
            '4'  => 'nullable|string|max:255',        // E = gender
            '5'  => 'nullable|string|max:255',        // F
            '6'  => 'nullable|string|max:255',        // G

            '7'  => 'nullable|string|max:255',        // H
            '8'  => 'nullable|string|max:255',        // I
            '9'  => 'nullable|string|max:255',        // J

            '10' => 'nullable|string|max:255',        // K
            '11' => 'nullable|string|max:255',        // L
            '12' => 'nullable|string|max:255',        // M
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
