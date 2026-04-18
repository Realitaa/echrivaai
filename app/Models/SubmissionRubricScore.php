<?php

namespace App\Models;

use Database\Factories\SubmissionRubricScoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionRubricScore extends Model
{
    /** @use HasFactory<SubmissionRubricScoreFactory> */
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'task_rubric_id',
        'score_ai',
        'score_teacher',
        'feedback_ai',
        'feedback_teacher',
    ];

    protected $casts = [
        'score_ai' => 'integer',
        'score_teacher' => 'integer',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(TaskRubric::class, 'task_rubric_id');
    }
}
