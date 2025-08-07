<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    protected $fillable = ['player_id', 'quest_id', 'times_started', 'times_completed'];
}
