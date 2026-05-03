<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $classroom = $this->route('classroom');
        $task = $this->route('task');

        return $classroom &&
            $classroom->teacher_id === auth()->id() &&
            $task &&
            $task->classroom_id === $classroom->id;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'deadline' => ['required', 'date', 'after_or_equal:today'],
            'is_published' => ['nullable', 'boolean'],
            'rubrics' => ['required', 'array', 'min:1'],
            'rubrics.*.title' => ['required', 'string', 'max:255', 'distinct'],
            'rubrics.*.description' => ['required', 'string'],
            'rubrics.*.max_score' => ['required', 'integer', 'min:1'],
            'rubrics.*.order' => ['required', 'integer', 'distinct'],
        ];
    }
}
