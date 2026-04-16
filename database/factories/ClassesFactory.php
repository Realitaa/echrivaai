<?php

namespace Database\Factories;

use App\Models\Classes;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Classes>
 */
class ClassesFactory extends Factory
{
    protected $model = Classes::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'teacher_id' => User::factory(),
            'code' => Str::upper(Str::random(6)),
        ];
    }
}
