<?php

namespace App\Services;

use Carbon\Carbon;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Service
{
    /**
    * generate otp method
    * private method
    * @return string
    */
    protected function generateOTP(): string
    {
        return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * send registration info 
     * to the user
     *
     * @param [string] $otp
     * @param [type] $user
     * @return void
    */
    protected function sendRegistrationInfo(string $otp, $user) :void
    {
        DB::beginTransaction();
    
        try {
            $user->otp = $otp;
            $user->otp_expire_at = Carbon::now()->addMinutes(5);
            $user->save();
            
            Mail::to($user->email)->send(new OtpMail($otp));
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to send OTP email: '.$e->getMessage());
            
            throw $e;
        }
    }
}
