<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\User;

class SidebarService
{
    public function getCachedSidebar(User $user): array
    {
        // Caching selama 1 jam berdasarkan ID user
        return Cache::remember("sidebar_user_v1_{$user->id}", now()->addHour(), function () use ($user) {
            // Logika multi-role
            if ($user->role === 'teacher') {
                return $user->teachingClasses()
                    ->select('id', 'name')
                    ->get()
                    ->map(fn($c) => [
                        'id' => $c->id,
                        'title' => $c->name,
                        'url' => route('teacher.classroom.show', $c->id),
                    ])
                    ->toArray();
            }

            return $user->enrollments()
                ->with('classroom')
                ->get()
                ->map(fn($enrollment) => [
                    'id' => $enrollment->classroom->id,
                    'title' => $enrollment->classroom->name,
                    'url' => route('student.classroom.show', $enrollment->classroom->id),
                ])
                ->toArray();
        });
    }
}