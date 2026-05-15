<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TemporaryFile;
use App\Models\File;
use App\Services\FileService;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function __construct(protected FileService $fileService) {}

    public function upload(Request $request)
    {
        $request->validate([
            'file' =>
                'required|file|max:20480|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,gif',
        ]);

        $temporaryFile = $this->fileService->putTempFile(
            $request->file('file'),
        );

        return response()->json([
            'success' => true,
            'file' => $temporaryFile,
        ]);
    }

    public function remove(TemporaryFile $file)
    {
        if ($file->uploaded_by != auth()->id()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Unauthorized',
                ],
                403,
            );
        }

        $this->fileService->deleteTempFileByName($file->filename);

        return response()->json([
            'success' => true,
        ]);
    }

    public function download($fileId)
    {
        $file = File::find($fileId);

        if (!$file || !Storage::disk('public')->exists($file->path)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => __('response.notFoundFile'),
                ],
                404,
            );
        }

        return Storage::disk('public')->download(
            $file->path,
            $file->original_name,
        );
    }
}
