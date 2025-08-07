<?php

namespace App\Http\Services;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class ItemService
{
    public function getTopItems(Request $request)
    {
        try {
            // IDs dos jogadores dos arquivos do usuário logado
            $playerIds = $request->user()
                ->uploadedLogsFiles()
                ->with('players:id,file_id')
                ->get()
                ->pluck('players')
                ->flatten()
                ->pluck('id');
    
            // Soma das quantidades agrupadas por item_name
            $topItems = Item::select('item_name', DB::raw('SUM(quantity) as total_quantity'))
                ->whereIn('player_id', $playerIds)
                ->groupBy('item_name')
                ->orderByDesc('total_quantity')
                ->limit(50)
                ->get();

            return $topItems;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getMostCollectedItems(Request $request)
    {
        try {
            $limit = $request->input('limit', 5); // padrão 10 itens
    
            // Busca os itens mais coletados, somando por nome e ordenando pela quantidade
            $items = Item::select('item_name')
                ->selectRaw('SUM(quantity) as total_collected')
                ->groupBy('item_name')
                ->orderByDesc('total_collected')
                ->limit($limit)
                ->get();
    
            return $items;
    
        } catch(\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}