<?php

namespace App\Exceptions;

use Exception;

/**
 * Excepción para errores en la generación de documentos
 */
class DocumentGenerationException extends Exception
{
    protected array $pendingRequirements = [];

    /**
     * Crea una excepción cuando el expediente no existe
     */
    public static function fileNotFound(int $fileId): self
    {
        return new self(
            "El expediente con ID {$fileId} no fue encontrado.",
            404
        );
    }

    /**
     * Crea una excepción cuando hay requisitos obligatorios incompletos
     */
    public static function mandatoryRequirementsIncomplete(array $requirements): self
    {
        $exception = new self(
            'No se puede generar la documentación. Existen requisitos obligatorios sin completar: ' .
            implode(', ', $requirements),
            422
        );
        $exception->pendingRequirements = $requirements;

        return $exception;
    }

    /**
     * Crea una excepción cuando no se encuentra una plantilla
     */
    public static function templateNotFound(string $templateName): self
    {
        return new self(
            "No se encontró la plantilla de documento: {$templateName}",
            404
        );
    }

    /**
     * Crea una excepción cuando falla la generación
     */
    public static function generationFailed(string $reason): self
    {
        return new self(
            "Error al generar los documentos: {$reason}",
            500
        );
    }

    /**
     * Obtiene los requisitos pendientes (si aplica)
     */
    public function getPendingRequirements(): array
    {
        return $this->pendingRequirements;
    }
}
