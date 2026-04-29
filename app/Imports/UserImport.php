<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class UserImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Validasi format file
        if (!array_key_exists('email', $row) || !array_key_exists('name', $row)) {
            throw new \Exception("Format file salah! Pastikan Anda mengunggah template USER ACCOUNT (kolom 'Email' atau 'Name' tidak ditemukan).");
        }

        // Skip empty rows and the warning/example row
        if (empty($row['email']) || empty($row['name']) || str_contains(strtoupper((string)$row['name']), 'CONTOH')) {
            return null;
        }

        $password = $row['password'] ?? 'Gmf12345'; // default password if not provided
        
        return User::updateOrCreate(
            [
                'email' => $row['email'],
            ],
            [
                'name' => $row['name'],
                'password' => Hash::make($password),
                'role' => strtolower($row['role'] ?? 'user'),
            ]
        );
    }
}
