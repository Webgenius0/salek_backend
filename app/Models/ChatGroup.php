<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    protected $fillable = ['user_one_id','user_two_id'];
}
