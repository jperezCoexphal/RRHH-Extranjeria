<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

/**
 * Servicio para la generación y manipulación de documentos PDF
 * Utiliza FPDI para rellenar formularios PDF existentes
 * y DomPDF para generar PDFs desde HTML/Blade
 */
class PdfGeneratorService
{
    /**
     * Directorio de plantillas PDF
     */
    private const TEMPLATES_PATH = 'pdf';

    /**
     * Directorio de salida para documentos generados
     */
    private const OUTPUT_PATH = 'generated_documents';

    /**
     * Genera un PDF desde una vista Blade
     *
     * @param  string  $view  Nombre de la vista Blade
     * @param  array  $data  Datos para la vista
     * @param  string  $outputFileName  Nombre del archivo de salida
     * @return string Ruta del archivo generado
     */
    public function generateFromView(string $view, array $data, string $outputFileName): string
    {
        $this->ensureOutputDirectoryExists();

        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('A4', 'portrait');

        $outputPath = self::OUTPUT_PATH . '/' . $outputFileName;
        Storage::put($outputPath, $pdf->output());

        return $outputPath;
    }

    /**
     * Genera un PDF desde HTML directo
     *
     * @param  string  $html  Contenido HTML
     * @param  string  $outputFileName  Nombre del archivo de salida
     * @return string Ruta del archivo generado
     */
    public function generateFromHtml(string $html, string $outputFileName): string
    {
        $this->ensureOutputDirectoryExists();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        $outputPath = self::OUTPUT_PATH . '/' . $outputFileName;
        Storage::put($outputPath, $pdf->output());

        return $outputPath;
    }

    /**
     * Rellena un formulario PDF existente con datos
     * Utiliza FPDI para importar el PDF y agregar texto en posiciones específicas
     *
     * @param  string  $templateName  Nombre del archivo de plantilla
     * @param  array  $fieldMappings  Mapeo de campos: ['campo' => ['x' => 0, 'y' => 0, 'page' => 1, 'value' => '']]
     * @param  string  $outputFileName  Nombre del archivo de salida
     * @return string Ruta del archivo generado
     */
    public function fillPdfForm(string $templateName, array $fieldMappings, string $outputFileName): string
    {
        $this->ensureOutputDirectoryExists();

        $templatePath = resource_path(self::TEMPLATES_PATH . '/' . $templateName);

        if (! file_exists($templatePath)) {
            throw new \Exception("La plantilla PDF no existe: {$templateName}");
        }

        $pdf = new Fpdi();

        // Importar todas las páginas del PDF original
        $pageCount = $pdf->setSourceFile($templatePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            // Agregar los campos de texto para esta página
            $this->addTextFields($pdf, $fieldMappings, $pageNo);
        }

        $outputPath = storage_path('app/' . self::OUTPUT_PATH . '/' . $outputFileName);
        $pdf->Output('F', $outputPath);

        return self::OUTPUT_PATH . '/' . $outputFileName;
    }

    /**
     * Agrega campos de texto al PDF en las posiciones especificadas
     */
    protected function addTextFields(Fpdi $pdf, array $fieldMappings, int $currentPage): void
    {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);

        foreach ($fieldMappings as $field) {
            // Solo procesar campos de la página actual
            $page = $field['page'] ?? 1;
            if ($page !== $currentPage) {
                continue;
            }

            $x = $field['x'] ?? 0;
            $y = $field['y'] ?? 0;
            $value = $field['value'] ?? '';
            $fontSize = $field['fontSize'] ?? 10;
            $fontStyle = $field['fontStyle'] ?? '';

            if (! empty($value)) {
                $pdf->SetFont('Helvetica', $fontStyle, $fontSize);
                $pdf->SetXY($x, $y);
                $pdf->Write(0, $this->sanitizeText($value));
            }
        }
    }

    /**
     * Genera el Modelo EX con los datos del expediente
     *
     * @param  array  $data  Datos del DocumentPackDTO->toTemplateData()
     * @param  string  $templateName  Nombre de la plantilla EX
     * @param  string  $outputFileName  Nombre del archivo de salida
     * @return string Ruta del archivo generado
     */
    public function generateModeloEX(array $data, string $templateName, string $outputFileName): string
    {
        // Mapeo de campos para el formulario EX-03 (ejemplo)
        // Estas coordenadas deben ajustarse según la plantilla específica
        $fieldMappings = $this->getModeloEXFieldMappings($data);

        return $this->fillPdfForm($templateName, $fieldMappings, $outputFileName);
    }

    /**
     * Obtiene el mapeo de campos para el Modelo EX
     * Las coordenadas deben ajustarse según la plantilla PDF específica
     */
    protected function getModeloEXFieldMappings(array $data): array
    {
        // Ejemplo de mapeo para EX-03
        // Las coordenadas (x, y) son en milímetros desde la esquina superior izquierda
        return [
            // Datos del solicitante (trabajador)
            ['x' => 45, 'y' => 52, 'page' => 1, 'value' => $data['apellidos'] ?? ''],
            ['x' => 45, 'y' => 58, 'page' => 1, 'value' => $data['nombre'] ?? ''],
            ['x' => 45, 'y' => 64, 'page' => 1, 'value' => $data['pasaporte'] ?? ''],
            ['x' => 130, 'y' => 64, 'page' => 1, 'value' => $data['nie'] ?? ''],
            ['x' => 45, 'y' => 70, 'page' => 1, 'value' => $data['fecha_nacimiento'] ?? ''],
            ['x' => 100, 'y' => 70, 'page' => 1, 'value' => $data['nacionalidad'] ?? ''],
            ['x' => 45, 'y' => 76, 'page' => 1, 'value' => $data['sexo'] ?? ''],
            ['x' => 100, 'y' => 76, 'page' => 1, 'value' => $data['estado_civil'] ?? ''],
            ['x' => 45, 'y' => 82, 'page' => 1, 'value' => $data['nombre_padre'] ?? ''],
            ['x' => 45, 'y' => 88, 'page' => 1, 'value' => $data['nombre_madre'] ?? ''],

            // Datos del empleador (página 2 típicamente)
            ['x' => 45, 'y' => 30, 'page' => 2, 'value' => $data['razon_social'] ?? ''],
            ['x' => 45, 'y' => 36, 'page' => 2, 'value' => $data['nif'] ?? ''],
            ['x' => 100, 'y' => 36, 'page' => 2, 'value' => $data['ccc'] ?? ''],

            // Datos laborales
            ['x' => 45, 'y' => 100, 'page' => 2, 'value' => $data['puesto_trabajo'] ?? ''],
            ['x' => 45, 'y' => 106, 'page' => 2, 'value' => $data['fecha_inicio'] ?? ''],
            ['x' => 100, 'y' => 106, 'page' => 2, 'value' => $data['fecha_fin'] ?? ''],
        ];
    }

    /**
     * Genera el Contrato de Trabajo como PDF
     */
    public function generateContrato(array $data, string $outputFileName): string
    {
        return $this->generateFromView('documents.contrato', $data, $outputFileName);
    }

    /**
     * Genera la Memoria Justificativa como PDF
     */
    public function generateMemoria(array $data, string $outputFileName): string
    {
        return $this->generateFromView('documents.memoria', $data, $outputFileName);
    }

    /**
     * Combina múltiples PDFs en uno solo
     *
     * @param  array  $pdfPaths  Array de rutas de PDFs a combinar
     * @param  string  $outputFileName  Nombre del archivo de salida
     * @return string Ruta del archivo combinado
     */
    public function mergePdfs(array $pdfPaths, string $outputFileName): string
    {
        $this->ensureOutputDirectoryExists();

        $pdf = new Fpdi();

        foreach ($pdfPaths as $pdfPath) {
            $fullPath = Storage::path($pdfPath);

            if (! file_exists($fullPath)) {
                continue;
            }

            $pageCount = $pdf->setSourceFile($fullPath);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }
        }

        $outputPath = storage_path('app/' . self::OUTPUT_PATH . '/' . $outputFileName);
        $pdf->Output('F', $outputPath);

        return self::OUTPUT_PATH . '/' . $outputFileName;
    }

    /**
     * Asegura que el directorio de salida existe
     */
    protected function ensureOutputDirectoryExists(): void
    {
        $outputDir = storage_path('app/' . self::OUTPUT_PATH);
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
    }

    /**
     * Sanitiza el texto para evitar problemas de codificación
     */
    protected function sanitizeText(string $text): string
    {
        // Convertir a ISO-8859-1 para FPDF (no soporta UTF-8 nativamente)
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
    }

    /**
     * Obtiene la información de un PDF (número de páginas, tamaño, etc.)
     */
    public function getPdfInfo(string $pdfPath): array
    {
        $fullPath = Storage::path($pdfPath);

        if (! file_exists($fullPath)) {
            return ['error' => 'Archivo no encontrado'];
        }

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($fullPath);

        return [
            'path' => $pdfPath,
            'pages' => $pageCount,
            'size' => filesize($fullPath),
            'modified' => date('Y-m-d H:i:s', filemtime($fullPath)),
        ];
    }
}
