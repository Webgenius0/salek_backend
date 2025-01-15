<?php

namespace App\Services;

class ImageService
{
    // public static function uploadeImage($file, $folder)
    // {
    //     $filename = time() . '.' . $file->getClientOriginalExtension();
    //     $file->move(public_path('uploads/' . $folder), $filename);
    //     $path = 'uploads/' . $folder . $filename;
    //     return $path;
    // }
    public static function uploadeImage($file, $folder)
    {
        // Generate a unique filename using time and a random string
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Move the file to the designated folder
        $file->move(public_path('uploads/' . $folder), $filename);

        // Return the file path
        $path = 'uploads/' . $folder . '/' . $filename;

        return $path;
    }

    /**
     * Delete a file from the public directory.
     *
     * @param string $filePath
     * @return bool
     */
    public static function deleteFile($filePath)
    {
        $fullPath = public_path($filePath);

        // Check if file exists, then delete it
        if (file_exists($fullPath)) {
            return unlink($fullPath); // Delete the file
        }

        return false; // Return false if file does not exist
    }
}
