<?php

namespace App\Models;

use Database\Factories\SubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Submission extends Model
{
    /** @use HasFactory<SubmissionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'task_id',
        'version',
        'is_latest',
        'content',
        'score_ai',
        'score_teacher',
        'final_score',
        'feedback_ai',
        'feedback_teacher',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'is_latest' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function aiFeedbacks(): HasMany
    {
        return $this->hasMany(AiFeedback::class);
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }
}
