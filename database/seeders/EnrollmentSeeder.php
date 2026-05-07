<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Classroom;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $class = Classroom::first();
        $students = User::where('role', 'student')->get();

        foreach ($students as $student) {
            Enrollment::updateOrCreate(
                [
                    'user_id' => $student->id,
                    'classroom_id' => $class->id,
                ],
                [
                    'role' => 'student',
                    'joined_at' => now(),
                ],
            );
        }
    }
}
