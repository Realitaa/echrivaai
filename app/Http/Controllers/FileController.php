<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TemporaryFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,gif',
        ]);

        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('tmp/', $filename, 'public');

        $temporaryFile = TemporaryFile::create([
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'file' => $temporaryFile,
        ]);
    }

    public function remove(TemporaryFile $file)
    {
        if ($file->uploaded_by != auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        Storage::disk('public')->delete('tmp/'.$file->filename);
        $file->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
