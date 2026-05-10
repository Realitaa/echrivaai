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
use Illuminate\Support\Facades\Storage;

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

        $promptContent = $submission->content ?: '(Tidak ada teks yang disertakan, silakan periksa dokumen lampiran.)';
        $attachments = [];

        $processFile = function ($file) use (&$promptContent, &$attachments) {
            $path = Storage::disk('public')->path($file->path);

            // Handle images
            if (str_starts_with($file->mime_type, 'image/')) {
                $attachments[] = new \Laravel\Ai\Files\LocalImage($path, $file->mime_type);
                return;
            }

            // Handle DOCX (Gemini doesn't support DOCX in inlineData, so we extract text)
            if ($file->mime_type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                $text = $this->extractTextFromDocx($path);
                $promptContent .= "\n\n[Isi dari dokumen {$file->original_name}]:\n{$text}";
                return;
            }

            // Handle other documents (like PDF) via Base64Document to ensure correct MIME type
            $attachments[] = new \Laravel\Ai\Files\Base64Document(
                base64_encode(file_get_contents($path)),
                $file->mime_type
            );
        };

        foreach ($task->files as $file) {
            $processFile($file);
        }

        foreach ($submission->files as $file) {
            $processFile($file);
        }

        try {
            $response = $agent->prompt($promptContent, $attachments);

            DB::transaction(function () use ($submission, $response, $promptContent) {
                // Save AI feedback
                $submission->aiFeedbacks()->create([
                    'result' => $response['feedback'],
                    'score' => $response['score'],
                    'model_name' => $response->meta->model ?? 'unknown',
                    'prompt' => $promptContent,
                ]);

                // Save rubric scores
                foreach ($response['rubric_scores'] as $rubricScore) {
                    $submission->rubricScores()->create([
                        'task_rubric_id' => $rubricScore['rubric_id'],
                        'score_ai' => $rubricScore['score'],
                        'feedback_ai' => $rubricScore['feedback'],
                    ]);
                }

                // Transition status to graded
                $submission->update(['status' => 'graded']);
            });
        } catch (\Throwable $e) {
            Log::error(
                "AI feedback generation failed for submission {$submission->id}: {$e->getMessage()}",
            );

            // Transition to failed so the student is not stuck in 'processing' forever
            $submission->update(['status' => 'failed']);
        }
    }

    /**
     * Extract plain text from a DOCX file.
     */
    private function extractTextFromDocx(string $path): string
    {
        $content = '';
        $zip = new \ZipArchive;

        if ($zip->open($path) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $data = $zip->getFromIndex($index);
                // Basic XML tag stripping for text extraction
                $content = strip_tags($data);
            }
            $zip->close();
        }

        return trim($content);
    }
}
