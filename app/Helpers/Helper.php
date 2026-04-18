<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Helper
{
    /**
     * Delete a file from public/uploads
     */
    public static function uploadFile($folder, $file): ?string
    {
        try {
            if (! $file || ! $file->isValid()) {
                throw new \Exception('Invalid file');
            }

            $basePath = "uploads/$folder";
            $fullPath = public_path($basePath);
            File::ensureDirectoryExists($fullPath);

            // file save
            $filename = time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();

            $file->move($fullPath, $filename);

            return $basePath.'/'.$filename;
        } catch (\Exception $e) {
            Log::error('File upload error: '.$e->getMessage());

            return null;
        }
    }

    public static function deleteFile(?string $filePath): bool
    {
        if (! $filePath) {
            return false; // nothing to delete
        }

        $fullPath = public_path($filePath);

        // Only unlink if it's a file
        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    /**
     * Generate a public URL for the uploaded file
     */
    public static function generateURL(?string $filePath): ?string
    {
        if (empty($filePath) || trim($filePath) === '') {
            return null;
        }

        // Remove any existing /storage/ or storage/ prefix from the path
        $filePath = ltrim($filePath, '/');
        if (str_starts_with($filePath, 'storage/')) {
            $filePath = substr($filePath, 8);
        }

        // If it's already a full URL, return it
        if (str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://')) {
            return $filePath;
        }

        // Return a full URL based on the relative path from public/
        return asset($filePath);
    }
}
