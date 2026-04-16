<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<File>
 */
class FileFactory extends Factory
{
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => 'uploads/' . fake()->uuid() . '.pdf',
            'filename' => fake()->word() . '.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(10000, 500000),
            'fileable_id' => null,
            'fileable_type' => null,
            'uploaded_by' => User::factory(),
        ];
    }
}
