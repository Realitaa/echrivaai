<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            abort(403);
        }

        if ($request->user()->role === 'teacher' && !$request->user()->is_approved && $request->route()->getName() !== 'register.pending') {
            return redirect()->route('register.pending');
        }

        if ($request->user()->role === 'teacher' && $request->user()->is_approved && $request->route()->getName() === 'register.pending') {
            return redirect()->route('teacher.classroom.index');
        }

        return $next($request);
    }
}
