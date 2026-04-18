<?php

namespace App\Models;

use Database\Factories\TaskRubricFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskRubric extends Model
{
    /** @use HasFactory<TaskRubricFactory> */
    use HasFactory;

    protected $fillable = [
        'task_id',
        'title',
        'description',
        'max_score',
        'order',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function submissionScores(): HasMany
    {
        return $this->hasMany(SubmissionRubricScore::class);
    }
}
