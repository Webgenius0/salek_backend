<?php

namespace App\Services;

use Illuminate\Support\Str;

class FileService extends Service
{
    public function fileUpload($file, $folder, $name)
    {
        if (!$file->isValid()) {
            return null;
        }

        $imageName = Str::slug($name) . '.' . $file->extension();

        $path = public_path('uploads/' . $folder);
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $file->move($path, $imageName);
        
        return 'uploads/' . $folder . '/' . $imageName;
    }
}
