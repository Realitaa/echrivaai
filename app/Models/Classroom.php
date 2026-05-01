<?php

namespace App\Models;

use Database\Factories\ClassesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    /** @use HasFactory<\Database\Factories\ClassroomFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'teacher_id',
        'code',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'classroom_id', 'user_id');
    }

    public function hasActiveTasks(): bool
    {
        return $this->tasks()->where('is_published', true)->exists();
    }

    public function publishedTasks()
    {
        return $this->hasMany(Task::class)->where('is_published', true);
    }
}
