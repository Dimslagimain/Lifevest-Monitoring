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
        // Skip empty rows and the warning/example row
        if (!isset($row['email']) || !isset($row['name']) || str_contains($row['name'], 'CONTOH PENGISIAN')) {
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
