<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_approved' => true,
            ],
        );

        // Teacher
        User::updateOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Teacher User',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'is_approved' => true,
            ],
        );

        // Students
        for ($i = 1; $i <= 5; $i++) {
            User::updateOrCreate(
                ['email' => "student$i@example.com"],
                [
                    'name' => "Student $i",
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'is_approved' => true,
                ],
            );
        }
    }
}
