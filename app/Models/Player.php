<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PlayerStat;

class Player extends Model
{
    protected $fillable = ['file_id', 'player_id', 'name', 'level'];

    public function stat() {
        return $this->hasOne(PlayerStat::class);
    }

    public function quests() {
        return $this->hasMany(Quest::class, 'player_id', 'player_id');
    }
}
