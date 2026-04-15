<?php

namespace App\Enums;

enum ClassRole: string
{
    case TEACHER = 'teacher';
    case STUDENT = 'student';
    case ASSISTANT = 'assistant';
}
