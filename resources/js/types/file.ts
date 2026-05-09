export type FileItem = {
    id: number;
    original_name: string;
    filename: string;
    isTemp?: boolean;
}

/**
 * Inertia type file response
 */
export type FileResponse = {
    success: boolean;
    file: {
        created_at: string;
        filename: string;
        id: number;
        original_name: string;
        updated_at: string;
        uploaded_by: number;
    }
}