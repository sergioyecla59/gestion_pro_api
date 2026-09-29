<?php

namespace App\Services;

use App\Models\Presupuesto;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Http\Response;

class PresupuestoPdfService
{
    /**
     * Construye la instancia del PDF con sus relaciones y opciones de formato.
     */
    public function generar(Presupuesto $presupuesto): DomPdfWrapper
    {
        $presupuesto->loadMissing([
            'empresa',
            'cliente',
            'lineas.producto',
        ]);

        $pdf = Pdf::loadView('pdf.presupuesto', [
            'presupuesto' => $presupuesto,
            'empresa' => $presupuesto->empresa,
            'cliente' => $presupuesto->cliente,
            'lineas' => $presupuesto->lineas,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Devuelve la respuesta HTTP para descargar el archivo PDF.
     */
    public function descargar(Presupuesto $presupuesto): Response
    {
        $pdf = $this->generar($presupuesto);
        $nombreArchivo = $this->obtenerNombreArchivo($presupuesto);

        return $pdf->download($nombreArchivo);
    }

    /**
     * Devuelve la respuesta HTTP para previsualizar el PDF en el navegador.
     */
    public function stream(Presupuesto $presupuesto): Response
    {
        $pdf = $this->generar($presupuesto);
        $nombreArchivo = $this->obtenerNombreArchivo($presupuesto);

        return $pdf->stream($nombreArchivo);
    }

    /**
     * Obtiene el contenido binario del PDF generado para adjuntos de correo.
     */
    public function obtenerContenidoBinario(Presupuesto $presupuesto): string
    {
        $pdf = $this->generar($presupuesto);

        return $pdf->output();
    }

    /**
     * Genera un nombre de archivo estándar y limpio.
     */
    public function obtenerNombreArchivo(Presupuesto $presupuesto): string
    {
        $numeroLimpio = preg_replace('/[^A-Za-z0-9_-]/', '_', $presupuesto->numero);

        return "Presupuesto_{$numeroLimpio}.pdf";
    }
}
