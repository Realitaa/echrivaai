<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user && $user->role === 'teacher' && !$user->is_approved) {
            return redirect()->route('register.pending');
        }

        $defaultRoute = match ($user->role) {
            'admin' => route('dashboard', absolute: false),
            'teacher' => route('teacher.classroom.index', absolute: false),
            'student' => route('student.classroom.index', absolute: false),
            default => config('fortify.home'),
        };

        return redirect()->intended($defaultRoute);
    }
}
