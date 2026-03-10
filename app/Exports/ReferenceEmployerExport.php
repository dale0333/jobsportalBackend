<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReferenceEmployerExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return [
            'LOC. NO',
            'COMPANY',
            'PROJECT INDUSTRY',
            'TOTAL DIRECT',
            'TOTAL INDIRECT',
            'TOTAL EXPAT',
            'TOTAL',
            'REMARKS',
        ];
    }

    public function array(): array
    {
        $rows = [];
        $grouped = $this->data->groupBy('industry');
        $format = fn($v) => ($v === null || $v === 0) ? '-' : $v;

        $grandTotals = [
            'direct' => 0,
            'indirect' => 0,
            'expat' => 0,
            'total' => 0,
        ];

        foreach ($grouped as $industry => $items) {

            $industryTotals = [
                'direct' => 0,
                'indirect' => 0,
                'expat' => 0,
                'total' => 0,
            ];

            foreach ($items as $item) {
                $rows[] = [
                    $item['loc_no'],
                    $item['company'],
                    $item['industry'],
                    $format($item['direct']),
                    $format($item['indirect']),
                    $format($item['expat']),
                    $format($item['total']),
                    $item['remarks']
                ];

                $industryTotals['direct'] += $item['direct'] ?: 0;
                $industryTotals['indirect'] += $item['indirect'] ?: 0;
                $industryTotals['expat'] += $item['expat'] ?: 0;
                $industryTotals['total'] += $item['total'] ?: 0;
            }

            // Project Industry TOTAL row (merged A:C)
            $rows[] = [
                'TOTAL ' . strtoupper($industry),
                '',
                '',
                $format($industryTotals['direct']),
                $format($industryTotals['indirect']),
                $format($industryTotals['expat']),
                $format($industryTotals['total']),
                '-',
            ];

            foreach ($industryTotals as $key => $value) {
                $grandTotals[$key] += $value;
            }
        }

        // Grand Total row (merged A:C)
        $rows[] = [
            'GRAND TOTAL',
            '',
            '',
            $format($grandTotals['direct']),
            $format($grandTotals['indirect']),
            $format($grandTotals['expat']),
            $format($grandTotals['total']),
            '-',
        ];

        // Blank row
        $rows[] = array_fill(0, 8, '');

        // LEGENDS & NOTES
        $rows[] = ['LEGENDS', '', '', 'NOTES:'];
        $rows[] = ['*', 'Updated', '', 'HANN PHILIPPINES INC. includes Clark Marriott and Swissotel Clark Phils. Inc.'];
        $rows[] = ['**', 'Not Updated', '', 'DONGGWANG CLARK CORPORATION includes Hilton Clark Sun Valley Resort'];
        $rows[] = ['^', 'Not Registered in the Jobs Portal', '', 'PREMIER CENTRAL INC. includes TENANTS under INDIRECT'];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Header row: dark green, white text
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF006400']],
        ]);
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 35,
            'C' => 25,
            'D' => 15,
            'E' => 17,
            'F' => 15,
            'G' => 12,
            'H' => 12,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = 'H';

                // Exclude LEGENDS/NOTES from borders
                $legendRow = $lastRow - 5;

                // Apply borders to data only
                $sheet->getStyle("A1:{$lastCol}{$legendRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '00000000'],
                        ],
                    ],
                ]);

                // Center numeric columns
                foreach (['A', 'D', 'E', 'F', 'G', 'H'] as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$legendRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Left align Company and Industry
                foreach (['B', 'C'] as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$legendRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Highlight TOTAL rows
                for ($row = 2; $row <= $legendRow; $row++) {
                    $cellValue = strtoupper($sheet->getCell("A{$row}")->getValue() ?? '');
                    if (str_contains($cellValue, 'TOTAL') && !str_contains($cellValue, 'GRAND')) {
                        // Merge A:C
                        $sheet->mergeCells("A{$row}:C{$row}");
                        $sheet->getStyle("A{$row}:H{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFFFF99'); // Light Yellow
                        $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
                        $sheet->getStyle("A{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    if (str_contains($cellValue, 'GRAND TOTAL')) {
                        $sheet->mergeCells("A{$row}:C{$row}");
                        $sheet->getStyle("A{$row}:H{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFADD8E6'); // Light Blue
                        $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
                        $sheet->getStyle("A{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }
            },
        ];
    }
}
