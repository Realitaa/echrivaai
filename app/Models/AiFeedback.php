<?php

namespace App\Models;

use Database\Factories\AiFeedbackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiFeedback extends Model
{
    /** @use HasFactory<AiFeedbackFactory> */
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'model_name',
        'prompt',
        'result',
        'score',
        'tokens_used',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
