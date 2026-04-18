<?php

namespace Database\Factories;

use App\Models\Submission;
use App\Models\User;
use App\Models\Task;
use App\Models\SubmissionRubricScore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'task_id' => Task::factory(),
            'version' => 1,
            'is_latest' => true,
            'content' => fake()->paragraphs(3, true),
            'status' => fake()->randomElement(['draft', 'submitted', 'graded']),
            'submitted_at' => now(),
        ];
    }

    public function withRubricScores(): static
    {
        return $this->afterCreating(function ($submission) {
            foreach ($submission->task->rubrics as $rubric) {
                SubmissionRubricScore::factory()->create([
                    'submission_id' => $submission->id,
                    'task_rubric_id' => $rubric->id,
                ]);
            }
        });
    }
}
