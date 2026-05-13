<?php

namespace App\Services\Teacher;

use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubmissionService
{
    public function getEnrolledStudentsWithSubmissions(Task $task)
    {
        return $task->classroom->students()
            ->with(['submissions' => function ($query) use ($task) {
                $query->where('task_id', $task->id)
                    ->with(['aiFeedbacks']);
            }])
            ->paginate(10)
            ->through(function ($student) use ($task) {
                $submissions = $student->submissions;
                
                // Calculate highest score for this student for this task
                $highestScore = $submissions->map(function ($submission) {
                    // Using score from aiFeedbacks (latest one or sum? usually it's one per submission)
                    return $submission->aiFeedbacks->max('score') ?? 0;
                })->max();

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'highest_score' => $highestScore,
                    'submission_count' => $submissions->count(),
                    'last_submission_at' => $submissions->max('submitted_at'),
                ];
            });
    }

    public function getStudentHistory(Task $task, User $student)
    {
        return $task->submissions()
            ->where('user_id', $student->id)
            ->with(['aiFeedbacks', 'files'])
            ->withSum('rubricScores as final_score', 'score_ai')
            ->orderBy('version', 'desc')
            ->get();
    }

    public function updateFeedback(Submission $submission, array $data)
    {
        return DB::transaction(function () use ($submission, $data) {
            foreach ($data['rubrics'] as $rubricData) {
                $submission
                    ->rubricScores()
                    ->where('task_rubric_id', $rubricData['task_rubric_id'])
                    ->update([
                        'score_teacher' => $rubricData['score_teacher'],
                        'feedback_teacher' => $rubricData['feedback_teacher'],
                    ]);
            }
            return true;
        });
    }
}
