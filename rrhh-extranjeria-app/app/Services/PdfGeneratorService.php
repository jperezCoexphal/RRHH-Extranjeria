<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Servicio para la generación de documentos PDF en caliente
 * Genera PDFs desde vistas Blade usando DomPDF
 * Los PDFs se generan en memoria y no se almacenan permanentemente
 */
class PdfGeneratorService
{
    /**
     * Genera un PDF desde una vista Blade y devuelve el contenido binario
     *
     * @param  string  $view  Nombre de la vista Blade
     * @param  array  $data  Datos para la vista
     * @return string Contenido binario del PDF
     */
    public function generateFromView(string $view, array $data): string
    {
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }

    /**
     * Genera el Modelo EX con los datos del expediente
     *
     * @param  array  $data  Datos del DocumentPackDTO->toTemplateData()
     * @return string Contenido binario del PDF
     */
    public function generateModeloEX(array $data): string
    {
        return $this->generateFromView('documents.modelo-ex', $data);
    }

    /**
     * Genera el Contrato de Trabajo como PDF
     *
     * @param  array  $data  Datos del DocumentPackDTO->toTemplateData()
     * @return string Contenido binario del PDF
     */
    public function generateContrato(array $data): string
    {
        return $this->generateFromView('documents.contrato', $data);
    }

    /**
     * Genera la Memoria Justificativa como PDF
     *
     * @param  array  $data  Datos del DocumentPackDTO->toTemplateData()
     * @return string Contenido binario del PDF
     */
    public function generateMemoria(array $data): string
    {
        return $this->generateFromView('documents.memoria', $data);
    }
}
