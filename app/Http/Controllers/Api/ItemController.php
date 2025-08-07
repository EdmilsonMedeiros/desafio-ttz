<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\ItemService;

class ItemController extends Controller
{
    private $itemService;
    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    /**
     * Get top items
     * @group Items
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse $items
     * 
     * @response 200 {
     *   "item_name": "health_potion",
     *   "total_quantity": "4190"
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 500 {
     *   "message": "Error message"
     * }
     */
    public function getTopItems(Request $request)
    {
        try{
            $items = $this->itemService->getTopItems($request);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }   

        return response()->json($items, 200);
    }

    public function getMostCollectedItems(Request $request)
    {
        try{
            $items = $this->itemService->getMostCollectedItems($request);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($items, 200);
    }
}
