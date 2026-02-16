<?php

namespace App\Imports;

use App\Models\{User, SubAttribute};
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class LocatorImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['email']) || empty($row['company_name'])) {
            return null;
        }

        $email = $this->makeUniqueEmail($row['email']);

        $industry = null;

        if (!empty($row['category'])) {
            $baseSlug = Str::slug($row['category']);
            $slug = $baseSlug;
            $counter = 1;

            while (SubAttribute::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $industry = SubAttribute::updateOrCreate(
                ['name' => $row['category'], 'attribute_id' => 1],
                [
                    'slug' => $slug,
                    'description' => $row['category'],
                    'is_active' => true,
                ]
            );
        }

        $user = User::create([
            'name' => $row['company_name'],
            'email' => $email,
            'address' => $row['address'] ?? null,
            'telephone' => $row['contact_no'] ?? null,
            'user_type' => 'employer',
            'email_verified_at' => now(),
            'password' => Hash::make(trim($row['password'] ?? 'password123'))
        ]);

        $contactPerson = trim(implode(' ', array_filter([
            ($row['last_name'] ?? ''),
            ($row['first_name'] ?? ''),
            ($row['middle_name'] ?? '')
        ])));

        $user->employer()->create([
            'contact_person' => $contactPerson,
            'position' => $row['position'] ?? null,
            'company_size' => 0,
            'locator_number' => $row['locno'] ?? null,
            'industry' => $industry->name ?? null,
            'sub_industry' => $row['subcategory'] ?? null,
        ]);

        return $user;
    }

    private function makeUniqueEmail(string $email): string
    {
        $email = trim(strtolower($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->generateFallbackEmail();
        }

        if (!User::where('email', $email)->exists()) {
            return $email;
        }

        [$name, $domain] = explode('@', $email, 2);
        $counter = 1;

        while (User::where('email', "{$name}{$counter}@{$domain}")->exists()) {
            $counter++;
        }

        return "{$name}{$counter}@{$domain}";
    }

    private function generateFallbackEmail(): string
    {
        do {
            $email = 'imported_' . uniqid() . '@example.com';
        } while (User::where('email', $email)->exists());

        return $email;
    }
}
