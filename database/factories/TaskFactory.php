<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'classroom_id' => Classroom::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'deadline' => fake()->dateTimeBetween('+1 days', '+1 month'),
            'created_by' => User::factory(),
            'is_published' => true,
            'minimal_score' => 85,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(
            fn() => [
                'is_published' => false,
            ],
        );
    }
}
