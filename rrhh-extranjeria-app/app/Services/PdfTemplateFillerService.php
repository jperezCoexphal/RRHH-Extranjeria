<?php

namespace App\Services;

use App\DTOs\DocumentPackDTO;
use App\Exceptions\PdfGenerationException;
use App\Models\PdfTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Servicio para rellenar plantillas PDF con datos usando pdftk
 */
class PdfTemplateFillerService
{
    protected string $pdftkPath;

    public function __construct()
    {
        $this->pdftkPath = config('services.pdftk.path', 'pdftk');
    }

    /**
     * Rellena una plantilla PDF con los datos proporcionados
     *
     * @param PdfTemplate $template Plantilla a rellenar
     * @param DocumentPackDTO $data Datos del expediente
     * @return string Contenido binario del PDF generado
     * @throws PdfGenerationException
     */
    public function fill(PdfTemplate $template, DocumentPackDTO $data): string
    {
        // Validar que la plantilla tiene el archivo
        if (!$template->file_exists) {
            throw PdfGenerationException::templateFileNotFound($template->storage_path);
        }

        // Obtener los datos mapeados para el PDF
        $fieldValues = $this->mapDataToFields($template, $data);

        // Generar el PDF
        return $this->generatePdf($template->file_path, $fieldValues);
    }

    /**
     * Rellena una plantilla PDF con valores directos (sin mapeo)
     *
     * @param string $templatePath Ruta al archivo PDF plantilla
     * @param array $fieldValues Array asociativo [campo => valor]
     * @return string Contenido binario del PDF generado
     */
    public function fillWithValues(string $templatePath, array $fieldValues): string
    {
        if (!file_exists($templatePath)) {
            throw PdfGenerationException::templateFileNotFound($templatePath);
        }

        return $this->generatePdf($templatePath, $fieldValues);
    }

    /**
     * Mapea los datos del DocumentPackDTO a los campos del PDF
     */
    protected function mapDataToFields(PdfTemplate $template, DocumentPackDTO $data): array
    {
        $fieldMapping = $template->field_mapping ?? [];
        $templateData = $data->toTemplateData();
        $fieldValues = [];

        foreach ($fieldMapping as $pdfFieldName => $mapping) {
            if (empty($mapping['field'])) {
                continue;
            }

            $source = $mapping['source'] ?? 'all';
            $dataField = $mapping['field'];
            $format = $mapping['format'] ?? null;

            // Obtener el valor según la fuente
            $value = $this->getValueFromSource($data, $templateData, $source, $dataField);

            // Aplicar formato si existe
            if ($value !== null && $format) {
                $value = $this->applyFormat($value, $format);
            }

            // Manejar checkboxes con condición "checked_when"
            if (isset($mapping['type']) && $mapping['type'] === 'checkbox') {
                if (isset($mapping['checked_when']) && $mapping['checked_when'] !== '') {
                    // Checkbox condicional: marcar solo si el valor coincide
                    $isChecked = (string) $value === (string) $mapping['checked_when'];
                    $value = $isChecked ? ($mapping['checked_value'] ?? 'Yes') : 'Off';
                } else {
                    // Checkbox simple: interpretar valor booleano
                    $value = $this->formatCheckboxValue($value, $mapping['checked_value'] ?? 'Yes');
                }
            }

            if ($value !== null) {
                $fieldValues[$pdfFieldName] = $value;
            }
        }

        return $fieldValues;
    }

    /**
     * Obtiene un valor de los datos según la fuente especificada
     */
    protected function getValueFromSource(
        DocumentPackDTO $data,
        array $templateData,
        string $source,
        string $field
    ): ?string {
        // Si está en los datos combinados, usarlo directamente
        if (isset($templateData[$field])) {
            return (string) $templateData[$field];
        }

        // Buscar en la fuente específica
        $sourceData = match ($source) {
            'worker' => $data->getWorkerData(),
            'employer' => $data->getEmployerData(),
            'job' => $data->getJobData(),
            'representative' => $data->getRepresentativeData(),
            default => $templateData,
        };

        return isset($sourceData[$field]) ? (string) $sourceData[$field] : null;
    }

    /**
     * Aplica un formato específico al valor
     */
    protected function applyFormat(string $value, string $format): string
    {
        return match ($format) {
            'uppercase' => mb_strtoupper($value),
            'lowercase' => mb_strtolower($value),
            'capitalize' => mb_convert_case($value, MB_CASE_TITLE),
            'date_dmy' => $this->formatDate($value, 'd/m/Y'),
            'date_ymd' => $this->formatDate($value, 'Y-m-d'),
            default => $value,
        };
    }

    /**
     * Formatea una fecha
     */
    protected function formatDate(string $value, string $format): string
    {
        try {
            $date = new \DateTime($value);
            return $date->format($format);
        } catch (\Exception) {
            return $value;
        }
    }

    /**
     * Formatea un valor para checkbox de PDF
     */
    protected function formatCheckboxValue(mixed $value, string $checkedValue): string
    {
        $isChecked = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            ?? ($value === '1' || $value === 'Si' || $value === 'Sí' || $value === 'Yes');

        return $isChecked ? $checkedValue : 'Off';
    }

    /**
     * Genera el PDF rellenado usando pdftk
     */
    protected function generatePdf(string $templatePath, array $fieldValues): string
    {
        // Crear archivo temporal para el FDF
        $fdfPath = $this->createFdfFile($fieldValues);

        // Crear archivo temporal para la salida
        $outputPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pdf_output_' . uniqid() . '.pdf';

        try {
            // Normalizar paths para Windows
            $normalizedTemplatePath = str_replace('/', DIRECTORY_SEPARATOR, $templatePath);

            $process = new Process([
                $this->pdftkPath,
                $normalizedTemplatePath,
                'fill_form',
                $fdfPath,
                'output',
                $outputPath,
                'flatten',
            ]);

            $process->setTimeout(120);
            $process->run();

            if ($process->getExitCode() !== 0) {
                $error = $process->getErrorOutput() ?: $process->getOutput();
                Log::error('pdftk fill_form failed', [
                    'template' => $templatePath,
                    'exit_code' => $process->getExitCode(),
                    'error' => $error,
                ]);
                throw PdfGenerationException::fillFormFailed($error, $process->getExitCode());
            }

            // Leer el PDF generado
            $content = file_get_contents($outputPath);

            if ($content === false) {
                throw PdfGenerationException::outputFailed($outputPath, 'No se pudo leer el archivo generado');
            }

            return $content;
        } finally {
            // Limpiar archivos temporales
            @unlink($fdfPath);
            @unlink($outputPath);
        }
    }

    /**
     * Crea un archivo FDF con los valores de los campos
     */
    protected function createFdfFile(array $fieldValues): string
    {
        $fdf = "%FDF-1.2\n";
        $fdf .= "1 0 obj\n";
        $fdf .= "<<\n";
        $fdf .= "/FDF\n";
        $fdf .= "<<\n";
        $fdf .= "/Fields [\n";

        foreach ($fieldValues as $fieldName => $value) {
            $escapedName = $this->escapeFdfString($fieldName);
            $escapedValue = $this->escapeFdfString($value);

            $fdf .= "<<\n";
            $fdf .= "/T ({$escapedName})\n";
            $fdf .= "/V ({$escapedValue})\n";
            $fdf .= ">>\n";
        }

        $fdf .= "]\n";
        $fdf .= ">>\n";
        $fdf .= ">>\n";
        $fdf .= "endobj\n";
        $fdf .= "trailer\n";
        $fdf .= "<<\n";
        $fdf .= "/Root 1 0 R\n";
        $fdf .= ">>\n";
        $fdf .= "%%EOF\n";

        $fdfPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fdf_' . uniqid() . '.fdf';
        file_put_contents($fdfPath, $fdf);

        return $fdfPath;
    }

    /**
     * Escapa una cadena para usar en FDF
     */
    protected function escapeFdfString(string $value): string
    {
        // Convertir a UTF-16BE con BOM para caracteres especiales
        if (preg_match('/[^\x00-\x7F]/', $value)) {
            $utf16 = mb_convert_encoding($value, 'UTF-16BE', 'UTF-8');
            return "\xFE\xFF" . $utf16;
        }

        // Escapar caracteres especiales de PDF
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('(', '\\(', $value);
        $value = str_replace(')', '\\)', $value);
        $value = str_replace("\r", '\\r', $value);
        $value = str_replace("\n", '\\n', $value);

        return $value;
    }

    /**
     * Genera una vista previa de los valores que se rellenarían
     */
    public function preview(PdfTemplate $template, DocumentPackDTO $data): array
    {
        return $this->mapDataToFields($template, $data);
    }
}
