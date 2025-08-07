<?php

namespace App\Http\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Models\Player;
use App\Models\PlayerStat;
use App\Models\Boss;
use App\Models\Quest;
use App\Models\Item;
use App\Models\Event;
use App\Models\Chat;
use App\Models\UploadedLogsFile;

class ImportService
{
    public function import(Request $request)
    {
        $file = $request->file('file');

        if (!$file) {
            throw new \Exception('No file was uploaded');
        }

        if ($file->getError() !== UPLOAD_ERR_OK) {
            $errorMessage = match ($file->getError()) {
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
                default => 'Unknown upload error'
            };
            throw new \Exception($errorMessage);
        }

        if (!$file->isValid()) {
            throw new \Exception('The uploaded file is not valid');
        }

        $path = $file->store('battle-quest-files/' . date('Y-m-d_H-i-s') . '_' . uniqid(), 'public');

        $uploadedLogsFile = auth()->user()->uploadedLogsFiles()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => $file->getMimeType(),
        ]);

        try {
            dispatch(new \App\Jobs\ParseLogFileJob(storage_path('app/public/' . $path), $uploadedLogsFile->id));
        } catch (\Exception $e) {
            $uploadedLogsFile->delete();
            Log::error('Dispatch failed: ' . $e->getMessage());
            throw new \Exception('Erro ao enfileirar processamento: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Arquivo importado com sucesso e será processado em breve.']);
    }

    public function parseFile($fullFilePath, $fileId)
    {
        Log::info('Parsing file: ' . $fullFilePath);

        if (!file_exists($fullFilePath)) {
            throw new \Exception("Arquivo não encontrado para parse.");
        }

        $handle = fopen($fullFilePath, "r");

        while (($line = fgets($handle)) !== false) {
            if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) \[(.*?)\] (\w+)\s+(.*)$/', $line, $matches)) {
                $timestamp = $matches[1];
                $category = $matches[2]; // Ex: COMBAT, CHAT, SYSTEM
                $eventType = $matches[3]; // Ex: BOSS_DEFEAT, MESSAGE
                $rest = $matches[4]; // key=value string

                $data = $this->parseKeyValueString($rest);

                match ($eventType) {
                    "PLAYER_JOIN" => $this->handlePlayerJoin($data, $fileId),
                    "BOSS_DEFEAT" => $this->handleBossDefeat($data, $fileId),
                    "QUEST_COMPLETE" => $this->handleQuestComplete($data, $fileId),
                    "ITEM_COLLECT", "ITEM_PICKUP" => $this->handleItemCollect($data, $fileId),
                    "CHAT", "MESSAGE" => $this->handleChat($data, $fileId, $timestamp),
                    default => $this->handleGenericEvent($eventType, $data, $fileId, $timestamp),
                };
            } else {
                Log::warning("Regex failed to match line", ['line' => $line]);
            }
        }

        fclose($handle);

        Log::info('File parsed successfully');
    }

    protected function parseKeyValueString(string $input): array
    {
        $data = [];
        preg_match_all('/(\w+)=(".*?"|\(.*?\)|\S+)/', $input, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $key = $match[1];
            $value = $match[2];

            if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                $value = trim($value, '"');
            }

            $data[$key] = $value;
        }

        return $data;
    }

    protected function handlePlayerJoin(array $data, $fileId)
    {
        $player = Player::firstOrCreate(
            [
                'file_id' => $fileId,
                'player_id' => $data['id'], // Not 'player_id'
            ],
            [
                'name' => $data['name'] ?? null,
                'level' => $data['level'] ?? null,
            ]
        );

        PlayerStat::firstOrCreate(
            ['player_id' => $player->id],
            ['xp_total' => 0, 'gold_total' => 0]
        );
    }

    protected function handleBossDefeat(array $data, $fileId)
    {
        $player = Player::where('file_id', $fileId)
            ->where('player_id', $data['player_id'] ?? $data['defeated_by'] ?? null)
            ->first();

        if (!$player) return;

        $boss = Boss::firstOrCreate(
            ['file_id' => $fileId, 'boss_name' => $data['boss_name'] ?? $data['boss']],
            []
        );

        $boss->increment('times_defeated');

        $stats = $player->stat;
        $stats->increment('xp_total', $data['xp'] ?? 0);
        $stats->increment('bosses_defeated');

        if (isset($data['points'])) {
            $stats->increment('points', $data['points']);
        }
    }

    protected function handleQuestComplete(array $data, $fileId)
    {
        $player = Player::where('file_id', $fileId)
            ->where('player_id', $data['player_id'])
            ->first();

        if (!$player) return;

        $quest = Quest::firstOrCreate(
            ['player_id' => $player->id, 'quest_id' => $data['quest_id'] ?? $data['quest']],
            []
        );
        $quest->increment('times_completed');

        $stats = $player->stat;
        $stats->increment('xp_total', $data['reward'] ?? $data['xp'] ?? 0);

        if (isset($data['points'])) {
            $stats->increment('points', $data['points']);
        }
    }

    protected function handleItemCollect(array $data, $fileId)
    {
        $player = Player::where('file_id', $fileId)
            ->where('player_id', $data['player_id'])
            ->first();

        if (!$player) return;

        $item = Item::firstOrCreate(
            ['player_id' => $player->id, 'item_name' => $data['item']],
            []
        );
        $item->increment('quantity', $data['quantity'] ?? $data['qty'] ?? 1);
    }

    protected function handleChat(array $data, $fileId, $timestamp)
    {
        $player = Player::where('file_id', $fileId)
            ->where('player_id', $data['player_id'])
            ->first();

        Chat::create([
            'file_id' => $fileId,
            'player_id' => $player?->id,
            'message' => $data['message'] ?? '',
            'sent_at' => $timestamp,
        ]);
    }

    protected function handleGenericEvent(string $eventType, array $data, $fileId, $timestamp)
    {
        Event::create([
            'file_id' => $fileId,
            'player_id' => null,
            'type' => $eventType,
            'data' => $data,
            'occurred_at' => $timestamp
        ]);
    }
}
