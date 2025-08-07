<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['file_id', 'player_id', 'type', 'data', 'occurred_at'];

    protected $casts = [
        'data' => 'array',
    ];
}
