<?php

namespace App\Http\Services;

use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\Boss;

class PlayerService
{
    public function getPlayers(Request $request)
    {
        try{
            // Obtém os IDs dos arquivos enviados pelo usuário logado
            $fileIds = $request->user()->uploadedLogsFiles()->pluck('id');
            // Busca todos os jogadores vinculados a esses arquivos
            $players = Player::whereIn('file_id', $fileIds)->get();

            return $players;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getLeaderboard(Request $request)
    {
        try {
            $players = $request->user()->uploadedLogsFiles()
                ->with(['players.stat', 'players.quests']) // 👈 inclui as quests
                ->get()
                ->pluck('players')
                ->flatten()
                ->filter(fn($player) => $player->stat)
                ->map(function ($player) {
                    $stat = $player->stat;
                    $questsCompleted = $player->quests->sum('times_completed'); // ✅ Soma total de vezes que concluiu quests
    
                    $score = $stat->xp_total
                        + ($stat->gold_total * 10)
                        + ($stat->kills_pvp * 500)
                        + ($stat->bosses_defeated * 300)
                        - ($stat->deaths * 100)
                        + ($stat->points ?? 0);
    
                    return [
                        'id' => $player->id,
                        'player_id' => $player->player_id,
                        'name' => $player->name,
                        'level' => $player->level,
                        'score' => $score,
                        'xp_total' => $stat->xp_total,
                        'gold_total' => $stat->gold_total,
                        'kills_pvp' => $stat->kills_pvp,
                        'deaths' => $stat->deaths,
                        'bosses_defeated' => $stat->bosses_defeated,
                        'points' => $stat->points ?? 0,
                        'quests_completed' => $questsCompleted,
                    ];
                })
                ->sortByDesc('score')
                ->values();
    
            return $players;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    
    
    public function getMostKilledBosses(Request $request)
    {
        try {
            $limit = $request->input('limit', 5); // padrão: top 5
    
            $bosses = Boss::select('boss_name', 'times_defeated')
                ->orderByDesc('times_defeated')
                ->limit($limit)
                ->get();
    
            return $bosses;
    
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    
}