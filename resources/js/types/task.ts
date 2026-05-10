export type Task = {
    id: number;
    title: string;
    description: string | null;
    deadline: string;
    is_published: boolean;
    created_at: string;
    classroom_id: number;
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