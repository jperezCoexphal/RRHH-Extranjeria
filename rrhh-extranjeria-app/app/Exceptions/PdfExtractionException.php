<?php

namespace App\Exceptions;

use Exception;

/**
 * Excepción para errores durante la extracción de campos de PDFs
 */
class PdfExtractionException extends Exception
{
    public function __construct(
        string $message = 'Error al extraer campos del PDF',
        int $code = 0,
        ?Exception $previous = null,
        public readonly ?string $filePath = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Crea una excepción para cuando pdftk no está instalado o no se encuentra
     */
    public static function pdftkNotFound(): self
    {
        return new self(
            'pdftk no está instalado o no se encuentra en el PATH del sistema. '
            . 'Por favor, instale pdftk y verifique que esté accesible desde la línea de comandos.'
        );
    }

    /**
     * Crea una excepción para cuando el archivo no existe
     */
    public static function fileNotFound(string $filePath): self
    {
        return new self(
            "El archivo PDF no existe o no es accesible: {$filePath}",
            filePath: $filePath
        );
    }

    /**
     * Crea una excepción para cuando el archivo no es un PDF válido
     */
    public static function invalidPdf(string $filePath): self
    {
        return new self(
            "El archivo no es un PDF válido o está corrupto: {$filePath}",
            filePath: $filePath
        );
    }

    /**
     * Crea una excepción para cuando el PDF no tiene campos de formulario
     */
    public static function noFieldsFound(string $filePath): self
    {
        return new self(
            "El PDF no contiene campos de formulario editables (AcroForm). "
            . "Asegúrese de usar un PDF con formularios interactivos.",
            filePath: $filePath
        );
    }

    /**
     * Crea una excepción para errores de ejecución de pdftk
     */
    public static function commandFailed(string $command, string $output, int $exitCode): self
    {
        return new self(
            "Error al ejecutar pdftk (código {$exitCode}): {$output}"
        );
    }

    /**
     * Crea una excepción para errores de parseo de la salida de pdftk
     */
    public static function parseError(string $details): self
    {
        return new self(
            "Error al procesar la salida de pdftk: {$details}"
        );
    }
}
