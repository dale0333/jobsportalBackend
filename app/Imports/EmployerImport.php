<?php

namespace App\Imports;

use App\Models\{User, Employer};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployerImport implements ToModel, WithHeadingRow
{
    public static $importedLocators = [];

    public function model(array $row)
    {
        $locator = $this->cleanLocator($row['number'] ?? null);

        // Track all imported locator numbers
        self::$importedLocators[] = $locator;

        $employer = Employer::where('locator_number', $locator)->first();

        if ($employer) {
            // Log::info("Updating employer with locator number {$locator}.");

            $employer->update([
                'industry' => $row['type'] ?? null,
                'locator_number' => $row['number'],
            ]);

            if ($employer->user) {
                $employer->user->update([
                    'name' => $row['name'] ?? null,
                ]);
            }
        } else {
            // Log::warning("Employer with locator number {$locator} not found. Creating new record.");

            $user = User::create([
                'user_type' => 'employer',
                'name' => $row['name'] ?? 'Unknown Employer',
                'email' => $this->generateFallbackEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
            ]);

            Log::info("Created user {$user->name} for employer with locator number {$locator}.");

            $user->employer()->create([
                'locator_number' => $row['number'],
                'industry' => $row['type'] ?? null,
            ]);
        }

        return null; // Prevents Laravel Excel from trying to insert a model automatically
    }

    private function cleanLocator($value)
    {
        if (!$value) return null;

        // Remove everything except numbers
        $value = preg_replace('/[^0-9]/', '', $value);

        // Remove leading zeros (0123 → 123)
        $value = ltrim($value, '0');

        return $value;
    }

    private function generateFallbackEmail(): string
    {
        do {
            $email = 'imported_' . uniqid() . '@example.com';
        } while (User::where('email', $email)->exists());

        return $email;
    }
}
