<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('backend.layouts.dashboard');
    }
}
