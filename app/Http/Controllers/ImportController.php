<?php

namespace App\Http\Controllers;

use App\Http\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
class ImportController extends Controller
{
    protected $importService;
    public function __construct(ImportService $importService) {
        $this->importService = $importService;
    }

    public function getImportedFiles(Request $request) {
        $page = $request->get('page', 1);
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search', '');

        $importedFiles = auth()->user()->uploadedLogsFiles()
        ->where('file_name', 'like', '%' . $search . '%')
        ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($importedFiles);
    }

    public function import(Request $request){
        try {
            $path = $this->importService->import($request);
            return redirect()->back()->with('success', 'Upload realizado com sucesso e dados estão sendo processados.');
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'File upload failed: ' . $e->getMessage());
        }
    }

    public function deleteImportedFile(Request $request, $id) {
        $importedFile = auth()->user()->uploadedLogsFiles()->find($id);
        $importedFile->delete();
        return redirect()->back()->with('success', 'File deleted successfully');
    }

    public function downloadFile(Request $request, $id) {
        $importedFile = auth()->user()->uploadedLogsFiles()->find($id);
        
        $filePath = storage_path('app/public/' . $importedFile->file_path);
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'O arquivo solicitado não está mais disponível.');
        }
        
        return response()->download($filePath);
    }
}
