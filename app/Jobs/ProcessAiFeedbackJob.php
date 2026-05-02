<?php

namespace App\Jobs;

use App\Ai\Agents\SubmissionFeedbackAgent;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessAiFeedbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(public Submission $submission) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $submission = $this->submission;
        $task = $submission->task;
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

        try {
            $response = $agent->prompt($submission->content);

            DB::transaction(function () use ($submission, $response) {
                // Save AI feedback
                $submission->aiFeedbacks()->create([
                    'result'     => $response['feedback'],
                    'score'      => $response['score'],
                    'model_name' => $response->meta->model ?? 'unknown',
                    'prompt'     => $submission->content,
                ]);

                // Save rubric scores
                foreach ($response['rubric_scores'] as $rubricScore) {
                    $submission->rubricScores()->create([
                        'task_rubric_id' => $rubricScore['rubric_id'],
                        'score_ai'       => $rubricScore['score'],
                        'feedback_ai'    => $rubricScore['feedback'],
                    ]);
                }

                // Transition status to graded
                $submission->update(['status' => 'graded']);
            });
        } catch (\Throwable $e) {
            Log::error("AI feedback generation failed for submission {$submission->id}: {$e->getMessage()}");

            // Transition to failed so the student is not stuck in 'processing' forever
            $submission->update(['status' => 'failed']);
        }
    }
}
