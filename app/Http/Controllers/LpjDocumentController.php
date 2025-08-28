<?php

namespace App\Http\Controllers;

use App\Models\Lpj;
use App\Services\LpjDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LpjDocumentController extends Controller
{
    protected $documentService;
    
    public function __construct(LpjDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }
    
    /**
     * Generate and download LPJ document
     */
    public function download(Lpj $lpj)
    {
        try {
            // Generate document
            $filePath = $this->documentService->generateDocument($lpj);
            $fullPath = storage_path('app/public/' . $filePath);
            
            // Check if file exists
            if (!file_exists($fullPath)) {
                return back()->with('error', 'Gagal membuat dokumen LPJ.');
            }
            
            // Generate download filename with format: {type} {no_surat} {kegiatan}.docx
            $downloadName = "{$lpj->type} {$lpj->no_surat} {$lpj->kegiatan}.docx";
            $downloadName = preg_replace('/[^A-Za-z0-9\-_.\s]/', '_', $downloadName);
            
            // Return file download
            return response()->download($fullPath, $downloadName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(false); // Keep file for future downloads
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat dokumen: ' . $e->getMessage());
        }
    }
    
    /**
     * Preview document (optional - for future enhancement)
     */
    public function preview(Lpj $lpj)
    {
        try {
            $filePath = $this->documentService->generateDocument($lpj);
            $fullPath = storage_path('app/public/' . $filePath);
            
            if (!file_exists($fullPath)) {
                return back()->with('error', 'Gagal membuat dokumen LPJ.');
            }
            
            // Return file for preview in browser
            return response()->file($fullPath, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
            ]);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat dokumen: ' . $e->getMessage());
        }
    }
    
    /**
     * Regenerate document (if template is updated)
     */
    public function regenerate(Lpj $lpj)
    {
        try {
            // Delete old document if exists
            $oldFiles = Storage::disk('public')->files('lpj-documents');
            foreach ($oldFiles as $file) {
                if (strpos($file, $lpj->no_surat) !== false) {
                    Storage::disk('public')->delete($file);
                }
            }
            
            // Generate new document
            $filePath = $this->documentService->generateDocument($lpj);
            
            return back()->with('success', 'Dokumen LPJ berhasil dibuat ulang.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat ulang dokumen: ' . $e->getMessage());
        }
    }
}
