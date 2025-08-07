<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Http\Services\EventService;

class EventController extends Controller
{
    private $eventService;
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Get all events
     * @group Events
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse $events
     * 
     * @response 200 {
     *   "id": 74107,
     *   "file_id": 1,
     *   "player_id": null,
     *   "type": "SERVER_ANNOUNCEMENT",
     *   "data": {
     *     "text": "Server maintenance in 10 minutes"
     *   },
     *   "occurred_at": "2025-08-31 11:39:30",
     *   "created_at": "2025-08-06T18:06:52.000000Z",
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
    public function index(Request $request)
    {
        try {
            $events = $this->eventService->getEvents($request);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($events, 200);
    }
}
