<?php

namespace App\Http\Services;

use Illuminate\Http\Request;
use App\Models\Event;

class EventService
{
    public function getEvents(Request $request)
    {
        try{
            $limit = intval($request->query('limit', 50));
            $limit = ($limit > 0 && $limit <= 500) ? $limit : 50; // segurança
    
            $events = Event::whereIn('file_id', $request->user()->uploadedLogsFiles()->pluck('id'))
                ->orderByDesc('occurred_at')
                ->limit($limit)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $events;
    }
}