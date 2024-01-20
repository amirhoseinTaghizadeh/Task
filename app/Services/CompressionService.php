<?php

namespace App\Services;

use App\Models\File;
use ZipArchive;
use PharData;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompressionService
{
    public function compressFile($filePath , $compressionType)
    {
        switch ($compressionType){
            case 1:
                return $this->compressZip($filePath);
            case 2:
                return $this->compress7Zip($filePath);
            case 3:
                return $this->compressTarGz($filePath);
            default:
                return null;
        }
        $this->updateFileStatus($filePath);
        return $compressedFilePath;
    }

    private function  compressZip($filePath){
        $originalFileNameWithoutExtension = pathinfo(basename($filePath), PATHINFO_FILENAME);

        $compressedFileName = $originalFileNameWithoutExtension . '.zip';
        $compressedFilePath = 'compressed/' . $compressedFileName;

        $zip = new ZipArchive();
        $zip->open(storage_path($compressedFilePath), ZipArchive::CREATE);

        $relativePath = 'uploads/' . basename($filePath);
        $fileToCompress = storage_path($relativePath);

        if (file_exists($fileToCompress)) {
            $zip->addFile($fileToCompress, basename($fileToCompress));
            $zip->close();

            Storage::disk('local')->delete($relativePath);

            return $compressedFilePath;
        } else {
            \Log::error("File not found for compression. File path: $fileToCompress");
            return response()->json(["message" => "There is a problem in file export"], 500);
        }
    }

    private function compress7Zip($filePath){
        $originalFileNameWithoutExtension = pathinfo(basename($filePath), PATHINFO_FILENAME);

        $compressedFileName = $originalFileNameWithoutExtension . '.7z';
        $compressedFilePath = 'compressed/' . $compressedFileName;

        Storage::disk('local')->copy($filePath, $compressedFilePath);

        return $compressedFilePath;
    }

    private function compressTarGz($filePath)
    {
        $originalFileNameWithoutExtension = pathinfo(basename($filePath), PATHINFO_FILENAME);

        $compressedFileName = $originalFileNameWithoutExtension . '.zip';
        $compressedFilePath = 'compressed/' . $compressedFileName;

        $zip = new ZipArchive;
        $zip->open(storage_path($compressedFilePath), ZipArchive::CREATE);

        $relativePath = 'uploads/' . basename($filePath);
        $fileToCompress = storage_path($relativePath);

        if (file_exists($fileToCompress)) {
            $zip->addFile($fileToCompress, basename($fileToCompress));
            $zip->close();
            Storage::disk('local')->delete($relativePath);

            return $compressedFilePath;
        } else {
            return null;
        }
    }

    private function updateFileStatus($filePath)
    {
        $originalFileName = basename($filePath);
        File::where('original_filename', $originalFileName)->update(['status' => true]);
    }
}
