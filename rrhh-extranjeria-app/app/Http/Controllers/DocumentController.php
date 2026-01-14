<?php

namespace App\Http\Controllers;

use App\Exceptions\DocumentGenerationException;
use App\Services\DocumentGenerationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ZipArchive;

/**
 * Controlador para la generación y descarga de documentos
 * Los documentos se generan en caliente y no se almacenan permanentemente
 */
class DocumentController extends Controller
{
    public function __construct(
        protected DocumentGenerationService $documentService,
    ) {}

    /**
     * Genera el pack completo de documentos y descarga como ZIP
     */
    public function generatePack(int $inmigrationFileId)
    {
        try {
            $representative = auth()->user();
            $result = $this->documentService->generateDocumentPack(
                $inmigrationFileId,
                $representative
            );

            // Crear ZIP en memoria
            $zipFileName = "expediente_{$result['file_code']}_" . now()->format('Ymd_His') . '.zip';
            $tempZipPath = sys_get_temp_dir() . '/' . $zipFileName;

            $zip = new ZipArchive();
            if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('No se pudo crear el archivo ZIP.');
            }

            // Añadir cada documento al ZIP
            foreach ($result['documents'] as $filename => $content) {
                $zip->addFromString($filename, $content);
            }

            $zip->close();

            // Devolver el ZIP para descarga y eliminarlo después
            return response()->download($tempZipPath, $zipFileName, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);

        } catch (DocumentGenerationException $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
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
     * Genera solo el Modelo EX y lo descarga directamente
     */
    public function generateModeloEX(int $inmigrationFileId)
    {
        try {
            $representative = auth()->user();
            $result = $this->documentService->generateModeloEX(
                $inmigrationFileId,
                $representative
            );

            return response($result['content'], 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $result['filename'] . '"',
            ]);

        } catch (DocumentGenerationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar Modelo EX: ' . $e->getMessage());
        }
    }

    /**
     * Verifica si se pueden generar los documentos
     */
    public function checkAvailability(int $inmigrationFileId)
    {
        $file = app(\App\Services\InmigrationFileService::class)->findById($inmigrationFileId);

        if (! $file) {
            return response()->json(['error' => 'Expediente no encontrado'], 404);
        }

        $file->load(['employer', 'foreigner', 'workAddress']);
        $availability = $this->documentService->checkAvailability($file);

        $canGenerate = ($availability['employer'] ?? false) && ($availability['foreigner'] ?? false);

        return response()->json([
            'can_generate' => $canGenerate,
            'availability' => $availability,
        ]);
    }
}
