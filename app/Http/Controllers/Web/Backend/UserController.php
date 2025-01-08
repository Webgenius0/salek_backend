<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::where('id', '!=', auth()->user()->id)->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('avatar', function ($data) {
                    if ($data->avatar) {
                        $url = asset($data->avatar);
                        return '<img src="' . $url . '" alt="avatar" width="50px" height="50px" style="margin-left:20px;">';
                    } else {
                        return '<img src="' . asset('default/logo.png') . '" alt="avatar" width="50px" height="50px" style="margin-left:20px;">';
                    }
                })
                ->addColumn('status', function ($data) {
                    $class = $data->status == "active" ? 'btn-success' : 'btn-danger';
                    $label = $data->status == "active" ? 'Active' : 'Inactive';

                    return '<button type="button" class="btn btn-sm ' . $class . '" onclick="showStatusChangeAlert(' . $data->id . ')" >' . $label . '</button>';
                })
                ->addColumn('action', function ($data) {
                    return '
                            <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                <a href="#" type="button" onclick="goToEdit(' . $data->id . ')" class="btn btn-primary fs-14 text-white delete-icn" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>
                            </div>
                        ';
                })
                ->rawColumns(['avatar', 'status', 'action'])
                ->make();
        }
        return view("backend.layouts.users.index");
    }

    public function new(Request $request)
    {
        if ($request->ajax()) {
            $data = User::where('id', '!=', auth()->user()->id)->where('role', 'trainer')->where('status', 'inactive')->where('is_new', 'yes')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('avatar', function ($data) {
                    if ($data->avatar) {
                        $url = asset($data->avatar);
                        return '<img src="' . $url . '" alt="avatar" width="50px" height="50px" style="margin-left:20px;">';
                    } else {
                        return '<img src="' . asset('default/logo.png') . '" alt="avatar" width="50px" height="50px" style="margin-left:20px;">';
                    }
                })
                ->addColumn('status', function ($data) {
                    $class = $data->status == "active" ? 'btn-success' : 'btn-danger';
                    $label = $data->status == "active" ? 'Active' : 'Inactive';

                    return '<button type="button" class="btn btn-sm ' . $class . '" onclick="showStatusChangeAlert(' . $data->id . ')" >' . $label . '</button>';
                })
                ->addColumn('action', function ($data) {
                    return '
                            <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                <a href="#" type="button" onclick="goToEdit(' . $data->id . ')" class="btn btn-primary fs-14 text-white delete-icn" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>
                            </div>
                        ';
                })
                ->rawColumns(['avatar', 'status', 'action'])
                ->make();
        }
        return view("backend.layouts.users.new");
    }

    public function create()
    {
        return view("backend.layouts.users.create");
    }

    public function store(Request $request)
    {
        //dd($request->all());

        $request->validate([
            'role'      => 'required|in:trainer',
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'gender'     => 'required|in:male,female',
            'password'  => 'required|confirmed|min:6'
        ]);

        $data = new User();
        $data->role = $request->role;
        $data->name = $request->name;
        $data->email = $request->email;
        $data->gender = $request->gender;
        $data->password = bcrypt($request->password);
        $data->email_verified_at = now();
        $data->status = 'active';
        $data->save();

        return redirect()->back()->with('t-success', 'User created successfully');
    }

    public function show(User $user, $id)
    {
        $data = User::findOrFail($id);
        return view("backend.layouts.users.show", compact('data'));
    }

    public function edit(User $user, $id){
        $data = User::findOrFail($id);
        return view("backend.layouts.users.edit", compact('data'));
    }

    public function update(Request $request, $id){
        $request->validate([
            'password' => 'required|confirmed|min:6'
        ]);
        $data = User::findOrFail($id);
        $data->password = bcrypt($request->password);
        $data->save();

        return redirect()->back()->with('t-success', 'Password updated successfully');
    }

    public function status(int $id): JsonResponse
    {
        $data = User::findOrFail($id);
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found.',
            ]);
        }
        $data->is_new = 'no';
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Your action was successful!',
        ]);
    }

    public function newCount()
    {
        $data = User::where('role', 'trainer')->where('status', 'inactive')->where('is_new', 'yes')->count();
        return response()->json([
            'status' => 'success',
            'message' => 'Your action was successful!',
            'data' => $data,
        ]);
    }
}
