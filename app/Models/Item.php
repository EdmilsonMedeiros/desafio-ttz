<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['file_id', 'player_id', 'item_name', 'quantity'];
}
