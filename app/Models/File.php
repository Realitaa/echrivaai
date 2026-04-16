<?php

namespace App\Models;

use Database\Factories\FileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    /** @use HasFactory<FileFactory> */
    use HasFactory;

    protected $fillable = [
        'path',
        'filename',
        'mime_type',
        'size',
        'fileable_id',
        'fileable_type',
        'uploaded_by',
    ];

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
