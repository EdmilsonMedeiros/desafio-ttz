<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['file_id', 'player_id', 'message', 'sent_at'];
}
