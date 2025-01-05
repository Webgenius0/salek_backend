<?php

namespace Database\Seeders;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        Setting::insert([
            [
                'id'               => 100,
                'project_name'     => 'Salek',
                'project_about'    => 'This is educational site',
                'subscription_fee' => 50,
                'project_switch'   => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]
        ]);
    }
}
