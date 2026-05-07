import type { Teacher } from './teacher';

interface Classroom {
    id: number;
    name: string;
    code: string;
    description: string;
    teacher_id: number;
    created_at: string;
    teacher: Teacher;
}

export type { Classroom };
