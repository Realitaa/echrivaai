<?php

namespace App\Ai\Agents;

use App\Models\Submission;
use App\Models\TaskRubric;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class SubmissionFeedbackAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        public Submission $submission,
        public array $rubrics = [],
        public ?Submission $previousSubmission = null,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $instructions = <<<PROMPT
        You are an academic essay grader and feedback provider for a classroom management system called Echrivaai.

        Your job is to:
        1. Evaluate the student's submission content and attached files.
        2. Provide a detailed narrative feedback in Bahasa Indonesia.
        3. Score each rubric criterion out of its maximum score.
        4. Provide an overall score (0-100).

        IMPORTANT RULES:
        - The score for each rubric criterion MUST NOT exceed its maximum score.
        - The rubric_id in your response MUST match the exact ID provided below.
        - If this is the first submission (no previous attempt), set progress_label to "Pertama".
        PROMPT;

        if ($this->previousSubmission) {
            $previousFeedback = $this->previousSubmission
                ->aiFeedbacks()
                ->first();
            $previousScore = $previousFeedback?->score ?? 0;

            $instructions .= "\n\n5. Compare with the student's previous attempt (score: {$previousScore}) and indicate whether the student has improved, declined, or remained the same.";
            $instructions .=
                "\n   - Use 'Meningkat' if improved, 'Menurun' if declined, 'Tetap' if unchanged.";
            $instructions .=
                "\n\nPrevious submission content:\n" .
                $this->previousSubmission->content;
        }

        $instructions .= "\n\nRubric criteria to evaluate:\n";
        foreach ($this->rubrics as $rubric) {
            $instructions .= "- ID: {$rubric->id} | {$rubric->title} (max: {$rubric->max_score}): {$rubric->description}\n";
        }

        return $instructions;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'feedback' => $schema->string()->required(),
            'score' => $schema->integer()->min(0)->max(100)->required(),
            'rubric_scores' => $schema
                ->array()
                ->items(
                    $schema->object([
                        'rubric_id' => $schema->integer()->required(),
                        'score' => $schema->integer()->required(),
                        'feedback' => $schema->string()->required(),
                    ]),
                )
                ->required(),
            'progress_label' => $schema->string()->required(),
        ];
    }
}
