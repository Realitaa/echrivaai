import type { SubmissionAIFeedback } from "./ai";
import type { FileItem } from "./file";
import type { TaskRubric } from "./task";

export type SubmissionItem = {
    id: number;
    version: number;
    status: string;
    content: string;
    submitted_at: string;
    is_latest: boolean;
    final_score?: number;
}

export type SubmissionDetail = {
    id: number;
    version: number;
    status: string;
    content: string;
    submitted_at: string;
    ai_feedbacks: SubmissionAIFeedback[];
    rubric_scores: SubmissionRubricScore[];
    files: FileItem[];
    final_score?: number;
}

export type SubmissionRubricScore = {
    id: number;
    task_rubric_id: number;
    score_ai: number | null;
    feedback_ai: string | null;
    score_teacher: number | null;
    feedback_teacher: string | null;
    rubric?: TaskRubric;
}