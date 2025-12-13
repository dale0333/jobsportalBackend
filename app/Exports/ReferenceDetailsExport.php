<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ReferenceDetailsExport implements FromCollection, WithHeadings, WithColumnWidths
{
    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function collection()
    {
        return $this->details->map(function ($detail) {
            return [
                'Company'            => $detail->company,
                'Name'               => $detail->name,
                'Category'           => $detail->category,
                'Position'           => $detail->position,
                'Nationality'        => $detail->nationality,
                'Gender'             => $detail->gender,
                'Domicile'           => $detail->domicile,
                'Status'             => $detail->status,
                'Temporary Address'  => $detail->tem_res_add,
                'Temporary Province' => $detail->tem_province,
                'Temporary Mun/Brgy' => $detail->tem_mun_brgy,
                'Permanent Address'  => $detail->per_res_add,
                'Permanent Province' => $detail->per_province,
                'Permanent Mun/Brgy' => $detail->per_mun_brgy,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Company',
            'Name',
            'Category',
            'Position',
            'Nationality',
            'Gender',
            'Domicile',
            'Status',
            'Temporary Address',
            'Temporary Province',
            'Temporary Mun/Brgy',
            'Permanent Address',
            'Permanent Province',
            'Permanent Mun/Brgy',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // Company
            'B' => 20, // Name
            'C' => 15, // Category
            'D' => 15, // Position
            'E' => 15, // Nationality
            'F' => 10, // Gender
            'G' => 20, // Domicile
            'H' => 10, // Status
            'I' => 30, // Temporary Address
            'J' => 20, // Temporary Province
            'K' => 20, // Temporary Mun/Brgy
            'L' => 30, // Permanent Address
            'M' => 20, // Permanent Province
            'N' => 20, // Permanent Mun/Brgy
        ];
    }
}
