<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Submission;
use App\Models\User;
use App\Models\Task;

class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $task = Task::first();
        $students = User::where('role', 'student')->get();

        foreach ($students as $student) {
            // version 1
            Submission::updateOrCreate(
                [
                    'user_id' => $student->id,
                    'task_id' => $task->id,
                    'version' => 1,
                ],
                [
                    'content' => 'Je suis étudiant...',
                    'status' => 'graded',
                    'is_latest' => false,
                ],
            );

            // version 2 (latest)
            Submission::updateOrCreate(
                [
                    'user_id' => $student->id,
                    'task_id' => $task->id,
                    'version' => 2,
                ],
                [
                    'content' => 'Je suis un étudiant motivé...',
                    'status' => 'submitted',
                    'is_latest' => true,
                ],
            );
        }
    }
}
