<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EmploymentReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $records;
    protected $filters;

    public function __construct($records, $filters)
    {
        $this->records = $records;
        $this->filters = $filters;
    }

    public function collection()
    {
        return collect($this->records->details);
    }

    public function headings(): array
    {
        return [
            'No.',
            'Employee Name',
            'Position / Rank',
            'Nationality',
            'Regular',
            'Probationary',
            'Contractual',
        ];
    }

    public function map($data): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $data->name ?? 'N/A',
            $data->position ?? 'N/A',
            $data->nationality ?? 'N/A',
            $data->status == 'Regular' ? 'X' : '',
            $data->status == 'Probationary' ? 'X' : '',
            $data->status == 'Contractual' ? 'X' : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFEAEAEA'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Body style
        $sheet->getStyle('A2:G' . ($this->records->details->count() + 1))->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Center status columns
        $sheet->getStyle('E2:G' . ($this->records->details->count() + 1))
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Freeze header row
        $sheet->freezePane('A2');
    }
}
