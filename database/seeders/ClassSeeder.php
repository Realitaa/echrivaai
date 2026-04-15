<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Classes;
use App\Models\User;
use Illuminate\Support\Str;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    $teacher = User::where('role', 'teacher')->first();

        Classes::create([
            'name' => 'French Writing A1',
            'description' => 'Basic French Writing Class',
            'teacher_id' => $teacher->id,
            'code' => Str::upper(Str::random(6)),
        ]);
    }
}
