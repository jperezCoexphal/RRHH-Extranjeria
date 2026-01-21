<?php

namespace App\Services;

use App\DTOs\PdfFieldDTO;
use App\Exceptions\PdfExtractionException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Servicio para extraer campos de formulario de PDFs AcroForms usando pdftk
 */
class PdfFieldExtractorService
{
    protected string $pdftkPath;

    public function __construct()
    {
        $this->pdftkPath = config('services.pdftk.path', 'pdftk');
    }

    /**
     * Extrae los campos de formulario de un archivo PDF
     *
     * @param string $filePath Ruta absoluta al archivo PDF
     * @return PdfFieldDTO[] Array de campos extraídos
     * @throws PdfExtractionException
     */
    public function extractFields(string $filePath): array
    {
        $this->validateFile($filePath);
        $this->checkPdftkAvailability();

        // Copiar a ruta temporal simple para evitar problemas con paths largos/especiales
        $tempPath = $this->copyToSimpleTempPath($filePath);

        try {
            $rawOutput = $this->executePdftk($tempPath);
            $fields = $this->parseOutput($rawOutput);

            if (empty($fields)) {
                throw PdfExtractionException::noFieldsFound($filePath);
            }

            return $fields;
        } finally {
            // Limpiar archivo temporal
            @unlink($tempPath);
        }
    }

    /**
     * Copia el archivo a una ruta temporal simple para evitar problemas con pdftk
     */
    protected function copyToSimpleTempPath(string $filePath): string
    {
        $tempDir = sys_get_temp_dir();
        $tempPath = $tempDir . DIRECTORY_SEPARATOR . 'pdf_' . uniqid() . '.pdf';

        if (!copy($filePath, $tempPath)) {
            throw PdfExtractionException::fileNotFound($filePath);
        }

        return $tempPath;
    }

    /**
     * Verifica que el archivo existe y es accesible
     */
    protected function validateFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw PdfExtractionException::fileNotFound($filePath);
        }

        if (!is_readable($filePath)) {
            throw PdfExtractionException::fileNotFound($filePath);
        }

        // Verificar que es un PDF válido (magic bytes)
        $handle = fopen($filePath, 'rb');
        $header = fread($handle, 5);
        fclose($handle);

        if ($header !== '%PDF-') {
            throw PdfExtractionException::invalidPdf($filePath);
        }
    }

    /**
     * Verifica que pdftk está instalado y disponible
     */
    protected function checkPdftkAvailability(): void
    {
        $process = new Process([$this->pdftkPath, '--version']);

        try {
            $process->run();
        } catch (\Exception $e) {
            throw PdfExtractionException::pdftkNotFound();
        }

        // pdftk retorna código 0 con --version
        // En algunos sistemas retorna información de versión con código diferente
        // Verificamos que al menos se ejecutó sin error fatal
        if ($process->getExitCode() !== 0 && $process->getExitCode() !== 1) {
            // Algunos sistemas retornan 1 con --version pero funcionan
            $output = $process->getOutput() . $process->getErrorOutput();
            if (stripos($output, 'pdftk') === false) {
                throw PdfExtractionException::pdftkNotFound();
            }
        }
    }

    /**
     * Ejecuta pdftk para extraer los campos del formulario
     */
    protected function executePdftk(string $filePath): string
    {
        // Normalizar path para Windows
        $normalizedPath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);

        $process = new Process([
            $this->pdftkPath,
            $normalizedPath,
            'dump_data_fields',
        ]);

        $process->setTimeout(60);

        try {
            $process->run();
        } catch (ProcessFailedException $e) {
            Log::error('pdftk execution failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
            throw PdfExtractionException::commandFailed(
                $process->getCommandLine(),
                $e->getMessage(),
                $process->getExitCode()
            );
        }

        if ($process->getExitCode() !== 0) {
            $errorOutput = $process->getErrorOutput();
            Log::error('pdftk returned non-zero exit code', [
                'file' => $filePath,
                'exit_code' => $process->getExitCode(),
                'error' => $errorOutput,
            ]);
            throw PdfExtractionException::commandFailed(
                $process->getCommandLine(),
                $errorOutput ?: 'Unknown error',
                $process->getExitCode()
            );
        }

        return $process->getOutput();
    }

    /**
     * Parsea la salida de pdftk dump_data_fields
     *
     * @return PdfFieldDTO[]
     */
    protected function parseOutput(string $output): array
    {
        $fields = [];
        $currentField = [];

        // Dividir por líneas y procesar
        $lines = explode("\n", $output);

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            // Inicio de un nuevo campo
            if ($line === '---') {
                if (!empty($currentField) && isset($currentField['FieldName'])) {
                    $fields[] = PdfFieldDTO::fromPdftkOutput($currentField);
                }
                $currentField = [];
                continue;
            }

            // Parsear línea clave: valor
            if (str_contains($line, ': ')) {
                [$key, $value] = explode(': ', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Manejar campos con múltiples valores (FieldStateOption)
                if ($key === 'FieldStateOption') {
                    if (!isset($currentField[$key])) {
                        $currentField[$key] = [];
                    }
                    $currentField[$key][] = $value;
                } else {
                    $currentField[$key] = $value;
                }
            }
        }

        // No olvidar el último campo
        if (!empty($currentField) && isset($currentField['FieldName'])) {
            $fields[] = PdfFieldDTO::fromPdftkOutput($currentField);
        }

        return $fields;
    }

    /**
     * Retorna información resumida de los campos extraídos
     */
    public function getSummary(array $fields): array
    {
        $summary = [
            'total' => count($fields),
            'by_type' => [],
            'required' => 0,
            'with_default_value' => 0,
        ];

        foreach ($fields as $field) {
            // Contar por tipo
            $type = $field->type;
            $summary['by_type'][$type] = ($summary['by_type'][$type] ?? 0) + 1;

            // Contar requeridos
            if ($field->isRequired) {
                $summary['required']++;
            }

            // Contar con valor por defecto
            if ($field->value !== null && $field->value !== '') {
                $summary['with_default_value']++;
            }
        }

        return $summary;
    }
}
