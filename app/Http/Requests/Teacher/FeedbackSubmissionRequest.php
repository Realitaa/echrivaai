<?php

namespace App\Http\Requests\Teacher;

use App\Models\TaskRubric;
use Illuminate\Foundation\Http\FormRequest;

class FeedbackSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $classroom = $this->route('classroom');
        $task = $this->route('task');
        $submission = $this->route('submission');

        return $classroom && $classroom->teacher_id === auth()->id() &&
               $task && $task->classroom_id === $classroom->id &&
               $submission && $submission->task_id === $task->id;
    }

    public function rules(): array
    {
        $rules = [
            'rubrics' => ['required', 'array', 'min:1'],
            'rubrics.*.task_rubric_id' => ['required', 'integer', 'exists:task_rubrics,id'],
            'rubrics.*.feedback_teacher' => ['required', 'string'],
        ];

        if ($this->has('rubrics') && is_array($this->rubrics)) {
            foreach ($this->rubrics as $index => $rubric) {
                if (isset($rubric['task_rubric_id'])) {
                    $maxScore = TaskRubric::find($rubric['task_rubric_id'])?->max_score ?? 100;
                    $rules["rubrics.{$index}.score_teacher"] = ['required', 'numeric', 'min:0', "max:{$maxScore}"];
                } else {
                    $rules["rubrics.{$index}.score_teacher"] = ['required', 'numeric', 'min:0'];
                }
            }
        }

        return $rules;
    }
}
