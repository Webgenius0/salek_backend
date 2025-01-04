<?php

namespace App\Http\Controllers\API;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingRequest;
use App\Traits\ApiResponse;

class SettingController extends Controller
{
    use ApiResponse;
    
    public function update(UpdateSettingRequest $request)
    {
        try {
            DB::beginTransaction();

            $name   = $request->input('project_name');
            $about  = $request->input('project_about');
            $fee    = $request->input('subscription_fee');
            $switch = $request->input('project_switch');

            $logo = null;
            if($request->hasFile('project_logo')){
                $logo = $request->file('project_logo');
            }

            $setting = Setting::latest()->first();

            if($setting){
                $setting->project_name     = $name;
                $setting->project_logo     = $logo;
                $setting->project_about    = $about;
                $setting->subscription_fee = $fee;
                $setting->project_switch   = $switch;

                $res = $setting->save();

                DB::commit();
                if($res){
                    return $this->successResponse(true, 'Setting update successfully', $setting, 200);
                }
                return $this->failedResponse('Something went wrong', 403);
            }

        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->failedDBResponse('Database error', $e->getMessage(), 403);
        }
    }
}
