<?php

namespace App\Exports;

use App\Models\{Category, SubAttribute};
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class StudentsExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $categories;
    protected $educations;

    public function __construct()
    {
        $this->categories = Category::all();
        $this->educations = SubAttribute::where('attribute_id', 2)->get();
    }

    /**
     * @return array
     */
    public function array(): array
    {
        // Return empty rows for users to fill (5 empty rows)
        return [
            ['', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', ''],
        ];
    }

    /**
     * Define the headings for the Excel file
     */
    public function headings(): array
    {
        return [
            'Category Expertise *',  // Required field with dropdown
            'Name *',                // Required field
            'Email *',               // Required field
            'Gender',                // Optional (Male/Female/Other)
            'Contact',               // Optional
            'Education Level',        // Optional
            'Field of Study',         // Optional
            'Skills',                 // Optional
        ];
    }

    /**
     * Apply styles to the Excel sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style for heading row
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF3498DB'],
            ],
        ]);

        // Style for instructions row (if you add one)
        $sheet->getStyle('A2:H2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 10,
            ],
        ]);

        // Style for empty data rows
        $sheet->getStyle('A3:H7')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFF9E6'], // Light yellow background
            ],
        ]);

        // Add borders to all cells
        $sheet->getStyle('A1:H7')
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Make required fields indicator
        $sheet->getStyle('A1:H1')->getFont()->getColor()->setARGB('FFFFFFFF');
    }

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 25, // Category Expertise
            'B' => 25, // Name
            'C' => 30, // Email
            'D' => 15, // Gender
            'E' => 20, // Contact
            'F' => 20, // Education Level
            'G' => 25, // Field of Study
            'H' => 30, // Skills
        ];
    }

    /**
     * Register events for dropdown creation
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Get all category names for dropdown
                $categoryNames = $this->categories->pluck('name')->toArray();
                $educationNames = $this->educations->pluck('name')->toArray();

                // Create a hidden sheet for dropdown values
                $sheet->getParent()->createSheet();
                $sheet->getParent()->setActiveSheetIndex(1);
                $hiddenSheet = $sheet->getParent()->getActiveSheet();
                $hiddenSheet->setTitle('HIDDEN_CATEGORIES');

                // Put category names in hidden sheet
                foreach ($categoryNames as $index => $category) {
                    $hiddenSheet->setCellValue('A' . ($index + 1), $category);
                }

                foreach ($educationNames as $index => $education) {
                    $hiddenSheet->setCellValue('B' . ($index + 1), $education);
                }

                // Go back to main sheet
                $sheet->getParent()->setActiveSheetIndex(0);

                // Apply dropdown to Category column (A) for rows 3-100
                for ($row = 3; $row <= 100; $row++) {
                    $validation = $sheet->getCell('A' . $row)->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('Invalid Category');
                    $validation->setError('Please select a category from the dropdown list');
                    $validation->setPromptTitle('Select Category');
                    $validation->setPrompt('Please select a category from the dropdown list');

                    // Reference the hidden sheet
                    $validation->setFormula1('HIDDEN_CATEGORIES!$A$1:$A$' . count($categoryNames));
                }

                // Apply dropdown for Education Level column (F)
                for ($row = 3; $row <= 100; $row++) {
                    $validation = $sheet->getCell('F' . $row)->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true); // optional field
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('Invalid Education');
                    $validation->setError('Please select a valid education level');
                    $validation->setPromptTitle('Select Education');
                    $validation->setPrompt('Choose from dropdown');

                    $validation->setFormula1('HIDDEN_CATEGORIES!$B$1:$B$' . count($educationNames));
                }

                // Apply dropdown for Gender column (D)
                $genders = ['Male', 'Female', 'Other'];
                for ($row = 3; $row <= 100; $row++) {
                    $validation = $sheet->getCell('D' . $row)->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true); // Allow blank for optional field
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"' . implode(',', $genders) . '"');
                }

                // Add instruction note
                $sheet->setCellValue('A2', 'Select from dropdown');
                $sheet->setCellValue('B2', 'Enter full name');
                $sheet->setCellValue('C2', 'Enter valid email');
                $sheet->setCellValue('D2', 'Select gender');
                $sheet->mergeCells('A2:H2');
                $sheet->getStyle('A2:H2')->getFont()->setItalic(true);

                // Freeze the header row
                $sheet->freezePane('A3');

                // Protect the hidden sheet
                $hiddenSheet = $sheet->getParent()->getSheetByName('HIDDEN_CATEGORIES');
                $hiddenSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);
            },
        ];
    }
}
