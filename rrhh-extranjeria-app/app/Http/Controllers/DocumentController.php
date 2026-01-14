<?php

namespace App\Http\Controllers;

use App\Exceptions\DocumentGenerationException;
use App\Services\DocumentGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentGenerationService $documentService,
    ) {}

    // VERIFICAR DISPONIBILIDAD
    /*-----------------------------------------------------------------------------------*/

    /**
     * Verifica si se pueden generar los documentos
     */
    public function checkAvailability(int $inmigrationFileId)
    {
        $result = $this->documentService->canGenerateDocuments($inmigrationFileId);

        if (request()->expectsJson()) {
            return response()->json($result);
        }

        return view('documents.availability', [
            'inmigration_file_id' => $inmigrationFileId,
            'can_generate' => $result['can_generate'],
            'pending_requirements' => $result['pending_requirements'],
        ]);
    }

    // GENERAR DOCUMENTOS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Genera el pack completo de documentos
     */
    public function generatePack(int $inmigrationFileId)
    {
        try {
            $representative = auth()->user();
            $result = $this->documentService->generateDocumentPack(
                $inmigrationFileId,
                $representative
            );

            if (request()->expectsJson()) {
                return response()->json($result);
            }

            return redirect()->route('inmigration-files.show', $inmigrationFileId)
                ->with('success', 'Pack de documentos generado exitosamente.')
                ->with('generated_files', $result['files']);
        } catch (DocumentGenerationException $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'pending_requirements' => $e->getPendingRequirements(),
                ], $e->getCode() ?: 422);
            }

            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error inesperado: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al generar documentos: ' . $e->getMessage());
        }
    }

    /**
     * Genera solo el Modelo EX
     */
    public function generateModeloEX(int $inmigrationFileId)
    {
        try {
            $representative = auth()->user();
            $result = $this->documentService->generateDocumentPack(
                $inmigrationFileId,
                $representative
            );

            // Devolver solo el Modelo EX
            $modeloExPath = $result['files']['modelo_ex'] ?? null;

            if (! $modeloExPath) {
                return back()->with('error', 'No se pudo generar el Modelo EX.');
            }

            return $this->downloadFile($modeloExPath);
        } catch (DocumentGenerationException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // LISTAR DOCUMENTOS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Lista los documentos generados para un expediente
     */
    public function listDocuments(int $inmigrationFileId, Request $request)
    {
        $file = app(\App\Services\InmigrationFileService::class)->findById($inmigrationFileId);

        if (! $file) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Expediente no encontrado'], 404);
            }
            return redirect()->route('inmigration-files.index')
                ->with('error', 'Expediente no encontrado.');
        }

        $documents = $this->documentService->getGeneratedDocuments($file->file_code);

        if ($request->expectsJson()) {
            return response()->json([
                'file_code' => $file->file_code,
                'documents' => $documents,
            ]);
        }

        return view('documents.list', compact('file', 'documents'));
    }

    // DESCARGAR DOCUMENTOS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Descarga un documento específico
     */
    public function download(Request $request)
    {
        $path = $request->get('path');

        if (! $path) {
            return back()->with('error', 'Ruta de documento no especificada.');
        }

        return $this->downloadFile($path);
    }

    /**
     * Descarga un documento por su nombre
     */
    public function downloadByName(int $inmigrationFileId, string $documentName)
    {
        $file = app(\App\Services\InmigrationFileService::class)->findById($inmigrationFileId);

        if (! $file) {
            return redirect()->route('inmigration-files.index')
                ->with('error', 'Expediente no encontrado.');
        }

        $documents = $this->documentService->getGeneratedDocuments($file->file_code);

        $document = collect($documents)->firstWhere('name', $documentName);

        if (! $document) {
            return back()->with('error', 'Documento no encontrado.');
        }

        return $this->downloadFile($document['path']);
    }

    /**
     * Descarga todos los documentos como ZIP
     */
    public function downloadAll(int $inmigrationFileId)
    {
        $file = app(\App\Services\InmigrationFileService::class)->findById($inmigrationFileId);

        if (! $file) {
            return redirect()->route('inmigration-files.index')
                ->with('error', 'Expediente no encontrado.');
        }

        $documents = $this->documentService->getGeneratedDocuments($file->file_code);

        if (empty($documents)) {
            return back()->with('error', 'No hay documentos para descargar.');
        }

        // Crear archivo ZIP
        $zipFileName = "expediente_{$file->file_code}_" . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Asegurar que existe el directorio temp
        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'No se pudo crear el archivo ZIP.');
        }

        foreach ($documents as $document) {
            $fullPath = Storage::path($document['path']);
            if (file_exists($fullPath)) {
                $zip->addFile($fullPath, $document['name']);
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    // ELIMINAR DOCUMENTOS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Elimina un documento específico
     */
    public function deleteDocument(Request $request)
    {
        $path = $request->get('path');

        if (! $path) {
            return back()->with('error', 'Ruta de documento no especificada.');
        }

        if (Storage::exists($path)) {
            Storage::delete($path);

            // También eliminar metadatos si existen
            $metaPath = $path . '.meta.json';
            if (Storage::exists($metaPath)) {
                Storage::delete($metaPath);
            }

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Documento eliminado.']);
            }

            return back()->with('success', 'Documento eliminado exitosamente.');
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Documento no encontrado.'], 404);
        }

        return back()->with('error', 'Documento no encontrado.');
    }

    // HELPERS
    /*-----------------------------------------------------------------------------------*/

    /**
     * Descarga un archivo desde storage
     */
    protected function downloadFile(string $path)
    {
        $fullPath = $this->documentService->downloadDocument($path);

        if (! $fullPath || ! file_exists($fullPath)) {
            return back()->with('error', 'El archivo no existe.');
        }

        $fileName = basename($fullPath);
        $mimeType = mime_content_type($fullPath);

        return response()->download($fullPath, $fileName, [
            'Content-Type' => $mimeType,
        ]);
    }
}
