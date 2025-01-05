<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'project_name',
        'project_logo',
        'project_about',
        'subscription_fee',
        'project_switch',
    ];
}
