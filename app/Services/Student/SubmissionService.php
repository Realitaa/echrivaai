<?php

namespace App\Services\Student;

use App\Ai\Agents\SubmissionFeedbackAgent;
use App\Models\Submission;
use App\Models\Task;
use App\Models\TemporaryFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubmissionService
{
    /**
     * Check if the student has a submission currently being processed.
     */
    public function hasProcessingSubmission(int $userId, int $taskId): bool
    {
        return Submission::where('user_id', $userId)
            ->where('task_id', $taskId)
            ->where('status', 'processing')
            ->exists();
    }

    /**
     * Create a new submission, move files, and trigger AI feedback.
     */
    public function createSubmission(Task $task, array $data): Submission
    {
        return DB::transaction(function () use ($task, $data) {
            $userId = auth()->id();

            // Calculate next version
            $latestVersion = Submission::where('user_id', $userId)
                ->where('task_id', $task->id)
                ->max('version') ?? 0;

            $nextVersion = $latestVersion + 1;

            // Mark all previous submissions as not latest
            Submission::where('user_id', $userId)
                ->where('task_id', $task->id)
                ->where('is_latest', true)
                ->update(['is_latest' => false]);

            // Create the submission
            $submission = Submission::create([
                'user_id'      => $userId,
                'task_id'      => $task->id,
                'version'      => $nextVersion,
                'is_latest'    => true,
                'content'      => $data['content'],
                'status'       => 'processing',
                'submitted_at' => now(),
            ]);

            // Move temporary files to permanent storage
            $this->attachFiles($submission, $data['temporary_file_ids']);

            // Trigger AI feedback
            $this->triggerAiFeedback($submission, $task);

            return $submission;
        });
    }

    /**
     * Move temporary files to permanent storage and attach to submission.
     */
    private function attachFiles(Submission $submission, array $tempFileIds): void
    {
        foreach ($tempFileIds as $tempFileId) {
            $tempFile = TemporaryFile::find($tempFileId);
            if (! $tempFile) {
                continue;
            }

            $oldPath = "tmp/{$tempFile->filename}";
            $newPath = "submissions/{$tempFile->filename}";

            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->move($oldPath, $newPath);
            }

            $submission->files()->create([
                'path'          => $newPath,
                'filename'      => $tempFile->filename,
                'original_name' => $tempFile->original_name,
                'mime_type'     => Storage::disk('public')->exists($newPath)
                    ? (Storage::disk('public')->mimeType($newPath) ?? 'application/octet-stream')
                    : 'application/octet-stream',
                'size'          => Storage::disk('public')->exists($newPath)
                    ? (Storage::disk('public')->size($newPath) ?? 0)
                    : 0,
                'uploaded_by'   => auth()->id(),
            ]);

            $tempFile->delete();
        }
    }

    /**
     * Trigger AI feedback processing for a submission.
     */
    private function triggerAiFeedback(Submission $submission, Task $task): void
    {
        $rubrics = $task->rubrics()->orderBy('order')->get();

        // Get previous submission for progress comparison
        $previousSubmission = Submission::where('user_id', $submission->user_id)
            ->where('task_id', $task->id)
            ->where('version', $submission->version - 1)
            ->first();

        $agent = new SubmissionFeedbackAgent(
            submission: $submission,
            rubrics: $rubrics->all(),
            previousSubmission: $previousSubmission,
        );

        $agent->prompt($submission->content);
    }

    /**
     * Get a submission with its relationships for AJAX detail view.
     */
    public function getSubmissionDetail(Submission $submission): array
    {
        $submission->load(['aiFeedbacks', 'rubricScores', 'files']);

        $progress = null;

        if ($submission->version > 1) {
            $previousSubmission = Submission::where('user_id', $submission->user_id)
                ->where('task_id', $submission->task_id)
                ->where('version', $submission->version - 1)
                ->first();

            if ($previousSubmission) {
                $previousFeedback = $previousSubmission->aiFeedbacks()->first();
                $currentFeedback = $submission->aiFeedbacks()->first();

                $previousScore = $previousFeedback?->score ?? 0;
                $currentScore = $currentFeedback?->score ?? 0;

                $label = match (true) {
                    $currentScore > $previousScore => 'Meningkat',
                    $currentScore < $previousScore => 'Menurun',
                    default => 'Tetap',
                };

                $progress = [
                    'previous_score' => $previousScore,
                    'current_score'  => $currentScore,
                    'label'          => $label,
                ];
            }
        }

        return [
            'submission' => $submission,
            'progress'   => $progress,
        ];
    }
}
