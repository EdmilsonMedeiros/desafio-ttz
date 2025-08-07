<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerStat extends Model
{
    protected $fillable = ['player_id', 'xp_total', 'gold_total', 'kills_pvp', 'deaths', 'bosses_defeated', 'points'];
}
