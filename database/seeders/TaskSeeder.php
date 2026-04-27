<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Classroom;
use App\Models\User;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $class = Classroom::first();
        $teacher = User::where('role', 'teacher')->first();

        Task::create([
            'classroom_id' => $class->id,
            'title' => 'Write About Yourself',
            'description' => 'Write a short essay in French about yourself.',
            'deadline' => now()->addDays(7),
            'minimal_score' => 85,
            'created_by' => $teacher->id,
            'is_published' => true,
        ]);
    }
}
