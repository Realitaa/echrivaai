import type { Classroom } from "./classroom";
import type { FileItem } from "./file";

export type Task = {
    id: number;
    title: string;
    description: string | null;
    deadline: string;
    is_published: boolean;
    created_at: string;
    classroom_id: number;
    classroom?: Classroom;
    files?: any[];
    rubrics?: any[];
}

export type TaskRubric = {
    id?: number;
    title: string;
    description: string;
    max_score: number;
    order: number;
}

export type TaskDetail = {
    id: number;
    title: string;
    description: string | null;
    deadline: string;
    is_published: boolean;
    created_at: string;
    classroom_id: number;
    files: FileItem[];
    rubrics: TaskRubric[];
    creator: {
        id: number;
        name: string;
    };
}