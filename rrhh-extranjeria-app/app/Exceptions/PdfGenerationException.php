<?php

namespace App\Exceptions;

use Exception;

/**
 * Excepción para errores durante la generación/relleno de PDFs
 */
class PdfGenerationException extends Exception
{
    public function __construct(
        string $message = 'Error al generar el PDF',
        int $code = 0,
        ?Exception $previous = null,
        public readonly ?string $templatePath = null,
        public readonly ?string $outputPath = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Crea una excepción para cuando la plantilla no existe
     */
    public static function templateNotFound(int $templateId): self
    {
        return new self(
            "No se encontró la plantilla PDF con ID: {$templateId}"
        );
    }

    /**
     * Crea una excepción para cuando el archivo de plantilla no existe
     */
    public static function templateFileNotFound(string $path): self
    {
        return new self(
            "El archivo de plantilla no existe: {$path}",
            templatePath: $path
        );
    }

    /**
     * Crea una excepción para cuando no hay plantilla predeterminada
     */
    public static function noDefaultTemplate(string $documentType, ?string $applicationType = null): self
    {
        $message = "No hay plantilla predeterminada para el tipo de documento: {$documentType}";

        if ($applicationType) {
            $message .= " y tipo de solicitud: {$applicationType}";
        }

        return new self($message);
    }

    /**
     * Crea una excepción para cuando el mapeo está incompleto
     */
    public static function incompleteMa(int $templateId, int $mappedFields, int $totalFields): self
    {
        return new self(
            "La plantilla {$templateId} tiene un mapeo incompleto: "
            . "{$mappedFields}/{$totalFields} campos mapeados"
        );
    }

    /**
     * Crea una excepción para errores al escribir el PDF de salida
     */
    public static function outputFailed(string $outputPath, string $reason): self
    {
        return new self(
            "Error al generar el PDF de salida: {$reason}",
            outputPath: $outputPath
        );
    }

    /**
     * Crea una excepción para errores de ejecución de pdftk fill_form
     */
    public static function fillFormFailed(string $output, int $exitCode): self
    {
        return new self(
            "Error al rellenar el formulario PDF (código {$exitCode}): {$output}"
        );
    }

    /**
     * Crea una excepción para errores genéricos de generación
     */
    public static function generationFailed(string $reason): self
    {
        return new self("Error al generar el documento: {$reason}");
    }
}
