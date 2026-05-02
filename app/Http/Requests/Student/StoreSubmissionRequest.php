<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TemporaryFile;

class StoreSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'content' => ['required', 'string'],
            'temporary_file_ids' => ['required', 'array', 'min:1'],
            'temporary_file_ids.*' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($userId) {
                    $tempFile = TemporaryFile::find($value);

                    if (! $tempFile) {
                        $fail('The selected file does not exist.');
                        return;
                    }

                    if ($tempFile->uploaded_by !== $userId) {
                        $fail('The selected file does not belong to you.');
                    }
                },
            ],
        ];
    }
}
