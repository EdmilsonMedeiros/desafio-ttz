<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boss extends Model
{
    protected $fillable = ['file_id', 'boss_name', 'times_defeated'];
}
