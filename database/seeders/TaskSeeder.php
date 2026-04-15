<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Classes;
use App\Models\User;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $class = Classes::first();
        $teacher = User::where('role', 'teacher')->first();

        Task::create([
            'class_id' => $class->id,
            'title' => 'Write About Yourself',
            'description' => 'Write a short essay in French about yourself.',
            'deadline' => now()->addDays(7),
            'max_score' => 100,
            'type' => 'essay',
            'created_by' => $teacher->id,
            'is_published' => true,
        ]);
    }
}
