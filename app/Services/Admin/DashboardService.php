<?php

namespace App\Services\Admin;

use App\Models\User;

class DashboardService
{
    public function getStat()
    {
        $stats = User::toBase()
            ->selectRaw("
                SUM(CASE WHEN role = ? THEN 1 ELSE 0 END) as admin,
                SUM(CASE WHEN role = ? THEN 1 ELSE 0 END) as teacher,
                SUM(CASE WHEN role = ? THEN 1 ELSE 0 END) as student,
                SUM(CASE WHEN role = ? AND is_approved = ? THEN 1 ELSE 0 END) as unapproved_teacher
            ", ['admin', 'teacher', 'student', 'teacher', false])
            ->first();

        return [
            'admin' => (int) $stats->admin,
            'teacher' => (int) $stats->teacher,
            'student' => (int) $stats->student,
            'unapproved_teacher' => (int) $stats->unapproved_teacher,
        ];
    }

    public function getNotApprovedTeacher()
    {
        return User::select('name', 'email', 'created_at')
            ->where('role', 'teacher')
            ->where('is_approved', false)
            ->get();
    }
}
