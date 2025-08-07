<?php
namespace App\Jobs;

use App\Http\Services\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ParseLogFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $fullFilePath;
    protected int $fileId;

    public function __construct(string $fullFilePath, int $fileId)
    {
        $this->fullFilePath = $fullFilePath;
        $this->fileId = $fileId;
    }

    public function handle()
    {
        $importService = new ImportService();
        $importService->parseFile($this->fullFilePath, $this->fileId);
    }
}
