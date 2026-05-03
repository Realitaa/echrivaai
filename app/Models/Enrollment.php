<?php

namespace App\Models;

use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['user_id', 'classroom_id', 'role', 'joined_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function isEnrolled($userId, $classroomId): bool
    {
        return Enrollment::where('user_id', $userId)
            ->where('classroom_id', $classroomId)
            ->exists();
    }
}
