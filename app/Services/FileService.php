<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\TemporaryFile;

class FileService
{
    public function putTempFile($file)
    {
        $fileName = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('tmp', $fileName, 'public');

        return TemporaryFile::create([
            'filename' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'uploaded_by' => auth()->id(),
        ]);
    }

    public function deleteTempFileById($fileId)
    {
        $file = TemporaryFile::where('id', $fileId)->first();
        if ($file) {
            Storage::disk('public')->delete('tmp/'.$file->filename);
            $file->delete();
        }
    }

    public function deleteTempFileByName($fileName)
    {
        Storage::disk('public')->delete('tmp/'.$fileName);
        TemporaryFile::where('filename', $fileName)->delete();
    }
}