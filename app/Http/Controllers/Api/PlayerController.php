<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Player;
use App\Http\Services\PlayerService;

class PlayerController extends Controller
{

    private $playerService;
    public function __construct(PlayerService $playerService)
    {
        $this->playerService = $playerService;
    }

    /**
     * Get all players
     * @group Players
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse $players
     * 
     * @response 200 {
     *   "id": 1,
     *   "file_id": 1,
     *   "player_id": "p6",
     *   "name": "Frank",
     *   "level": 39,
     *   "created_at": "2025-08-06T17:35:14.000000Z",
     *   "updated_at": "2025-08-06T17:35:14.000000Z"
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 500 {
     *   "message": "Error message"
     * }
     */
    public function index(Request $request)
    {
        try {
            $players = $this->playerService->getPlayers($request);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($players, 200);
    }

    /**
     * Get player stats
     * @group Players
     * @param Request $request
     * @param Player $player
     * @return \Illuminate\Http\JsonResponse $stats
     * 
     * @response 200 {
     *   "id": 5,
     *   "player_id": 5,
     *   "xp_total": 4175432,
     *   "gold_total": 0,
     *   "kills_pvp": 0,
     *   "deaths": 0,
     *   "bosses_defeated": 1261,
     *   "created_at": "2025-08-06T17:35:16.000000Z",
     *   "updated_at": "2025-08-06T18:06:52.000000Z"
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 500 {
     *   "message": "Error message"
     * }
     */
    public function stats(Request $request, Player $player)
    {
        try{
            $stats = $player->stat;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($stats, 200);
    }

    /**
     * Get leaderboard
     * @group Players
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse $players
     * 
     * @response 200 {
     *   "id": 2,
     *   "player_id": "p1",
     *   "name": "Alice",
     *   "level": 5,
     *   "score": 4584827,
     *   "xp_total": 4206527,
     *   "gold_total": 0,
     *   "kills_pvp": 0,
     *   "deaths": 0,
     *   "bosses_defeated": 1261
     * },
     * 
     *   "id": 5,
     *   "player_id": "p2",
     *   "name": "Bob",
     *   "level": 25,
     *   "score": 4553732,
     *   "xp_total": 4175432,
     *   "gold_total": 0,
     *   "kills_pvp": 0,
     *   "deaths": 0,
     *   "bosses_defeated": 1261
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 500 {
     *   "message": "Error message"
     * }
     */
    public function leaderboard(Request $request)
    {
        try {
            // Busca os players do usuário autenticado com suas estatísticas
            $players = $this->playerService->getLeaderboard($request);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($players, 200);
    }

    public function getMostKilledBosses(Request $request){
        try{
            $bosses = $this->playerService->getMostKilledBosses($request);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($bosses, 200);
    }
}
