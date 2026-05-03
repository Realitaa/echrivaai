<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskRubricFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'max_score' => fake()->randomElement([10, 20, 30, 40]),
            'order' => fake()->numberBetween(1, 5),
        ];
    }

    public function ordered(int $order): static
    {
        return $this->state(
            fn() => [
                'order' => $order,
            ],
        );
    }
}
