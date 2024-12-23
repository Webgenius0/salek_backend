<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class HelperService extends Service
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public static function fileUpload($file, $path)
    {
        $fileName = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($path), $fileName);
        return $path . '/' . $fileName;
    }

    public function checkUser()
    {
        if (!$this->user) {
            Auth::logout();
        }
    }
}
