<?php

namespace Database\Factories;

use App\Models\Submission;
use App\Models\User;
use App\Models\Task;
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
            'score_ai' => fake()->numberBetween(60, 100),
            'score_teacher' => null,
            'final_score' => null,
            'feedback_ai' => fake()->paragraph(),
            'feedback_teacher' => null,
            'status' => fake()->randomElement(['draft', 'submitted', 'graded']),
            'submitted_at' => now(),
        ];
    }

    public function graded(): static
    {
        return $this->state(fn () => [
            'score_teacher' => fake()->numberBetween(60, 100),
            'final_score' => fake()->numberBetween(60, 100),
            'feedback_teacher' => fake()->paragraph(),
            'status' => 'graded',
        ]);
    }
}
