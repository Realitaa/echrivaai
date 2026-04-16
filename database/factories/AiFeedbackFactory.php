<?php

namespace Database\Factories;

use App\Models\AiFeedback;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiFeedback>
 */
class AiFeedbackFactory extends Factory
{
    protected $model = AiFeedback::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'model_name' => 'gpt-4.1',
            'prompt' => fake()->paragraph(),
            'result' => fake()->paragraph(),
            'score' => fake()->numberBetween(60, 100),
            'tokens_used' => fake()->numberBetween(100, 1000),
        ];
    }
}
