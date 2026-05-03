<?php

namespace Database\Factories;

use App\Models\Submission;
use App\Models\TaskRubric;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskRubric>
 */
class SubmissionRubricScoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'task_rubric_id' => TaskRubric::factory(),

            'score_ai' => fake()->numberBetween(0, 40),
            'score_teacher' => fake()->optional()->numberBetween(0, 40),

            'feedback_ai' => fake()->sentence(),
            'feedback_teacher' => fake()->optional()->sentence(),
        ];
    }

    public function aiOnly(): static
    {
        return $this->state(
            fn() => [
                'score_teacher' => null,
                'feedback_teacher' => null,
            ],
        );
    }

    public function teacherOnly(): static
    {
        return $this->state(
            fn() => [
                'score_ai' => null,
                'feedback_ai' => null,
            ],
        );
    }

    public function reviewed(): static
    {
        return $this->state(
            fn() => [
                'score_teacher' => fake()->numberBetween(0, 40),
                'feedback_teacher' => fake()->sentence(),
            ],
        );
    }
}
