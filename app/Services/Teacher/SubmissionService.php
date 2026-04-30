<?php

namespace App\Services\Teacher;

use App\Models\Submission;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class SubmissionService
{
    public function getPaginatedSubmissions(Task $task)
    {
        return $task->submissions()->with('user')->paginate(10);
    }

    public function updateFeedback(Submission $submission, array $data)
    {
        return DB::transaction(function () use ($submission, $data) {
            foreach ($data['rubrics'] as $rubricData) {
                $submission->rubricScores()
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
