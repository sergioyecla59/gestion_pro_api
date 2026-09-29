<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presupuesto;
use App\Services\PresupuestoPdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PresupuestoPublicoController extends Controller
{
    /**
     * Obtener los datos públicos de un presupuesto a través de su token único.
     */
    public function show(string $token): JsonResponse
    {
        $presupuesto = Presupuesto::where('token', $token)
            ->with([
                'empresa:id,nombre_comercial,razon_social,nif,email,telefono,direccion,codigo_postal,localidad,provincia,pais',
                'cliente:id,nombre,nif,email,telefono,direccion,codigo_postal,localidad,provincia,pais',
                'lineas.producto:id,referencia,nombre',
            ])
            ->first();

        if (! $presupuesto) {
            return response()->json([
                'message' => 'Presupuesto no encontrado.',
            ], 404);
        }

        // Un presupuesto en borrador aún no está publicado para el cliente
        if ($presupuesto->estado === 'Borrador') {
            return response()->json([
                'message' => 'Este presupuesto no está disponible para visualización pública.',
            ], 403);
        }

        return response()->json([
            'presupuesto' => $presupuesto,
            'caducado' => $presupuesto->estaCaducado(),
        ]);
    }

    /**
     * Aceptar el presupuesto online.
     */
    public function aceptar(string $token): JsonResponse
    {
        $presupuesto = Presupuesto::where('token', $token)->first();

        if (! $presupuesto) {
            return response()->json([
                'message' => 'Presupuesto no encontrado.',
            ], 404);
        }

        if ($presupuesto->estado === 'Borrador') {
            return response()->json([
                'message' => 'El presupuesto se encuentra en borrador y no puede ser aceptado.',
            ], 403);
        }

        if ($presupuesto->estado === 'Aceptado') {
            return response()->json([
                'message' => 'Este presupuesto ya fue aceptado anteriormente.',
                'fecha_aceptacion' => $presupuesto->fecha_aceptacion,
            ], 422);
        }

        if ($presupuesto->estado === 'Rechazado') {
            return response()->json([
                'message' => 'Este presupuesto fue rechazado y no puede ser aceptado.',
                'fecha_rechazo' => $presupuesto->fecha_rechazo,
            ], 422);
        }

        // Comprobación de caducidad
        if ($presupuesto->estaCaducado()) {
            return response()->json([
                'message' => 'El presupuesto ha caducado y no puede ser aceptado.',
                'valido_hasta' => $presupuesto->valido_hasta?->format('Y-m-d'),
            ], 422);
        }

        // Asignación controlada internamente (sin mass assignment)
        $presupuesto->estado = 'Aceptado';
        $presupuesto->fecha_aceptacion = now();
        $presupuesto->save();

        return response()->json([
            'message' => 'Presupuesto aceptado correctamente.',
            'presupuesto' => $presupuesto->fresh([
                'empresa:id,nombre_comercial,email,telefono',
                'cliente:id,nombre',
                'lineas.producto:id,referencia,nombre',
            ]),
        ]);
    }

    /**
     * Rechazar el presupuesto online con motivo opcional.
     */
    public function rechazar(Request $request, string $token): JsonResponse
    {
        $request->validate([
            'motivo' => ['nullable', 'string', 'max:1000'],
        ]);

        $presupuesto = Presupuesto::where('token', $token)->first();

        if (! $presupuesto) {
            return response()->json([
                'message' => 'Presupuesto no encontrado.',
            ], 404);
        }

        if ($presupuesto->estado === 'Borrador') {
            return response()->json([
                'message' => 'El presupuesto se encuentra en borrador y no puede ser rechazado.',
            ], 403);
        }

        if ($presupuesto->estado === 'Aceptado') {
            return response()->json([
                'message' => 'Este presupuesto ya fue aceptado y no puede ser rechazado.',
                'fecha_aceptacion' => $presupuesto->fecha_aceptacion,
            ], 422);
        }

        if ($presupuesto->estado === 'Rechazado') {
            return response()->json([
                'message' => 'Este presupuesto ya fue rechazado anteriormente.',
                'fecha_rechazo' => $presupuesto->fecha_rechazo,
            ], 422);
        }

        // Comprobación de caducidad
        if ($presupuesto->estaCaducado()) {
            return response()->json([
                'message' => 'El presupuesto ha caducado y no admite nuevas respuestas.',
                'valido_hasta' => $presupuesto->valido_hasta?->format('Y-m-d'),
            ], 422);
        }

        // Asignación controlada internamente (sin mass assignment)
        $presupuesto->estado = 'Rechazado';
        $presupuesto->fecha_rechazo = now();
        $presupuesto->motivo_rechazo = $request->input('motivo');
        $presupuesto->save();

        return response()->json([
            'message' => 'Presupuesto rechazado correctamente.',
            'presupuesto' => $presupuesto->fresh([
                'empresa:id,nombre_comercial,email,telefono',
                'cliente:id,nombre',
                'lineas.producto:id,referencia,nombre',
            ]),
        ]);
    }

    /**
     * Descarga pública del PDF del presupuesto para el cliente.
     */
    public function descargarPdf(
        Request $request,
        string $token,
        PresupuestoPdfService $pdfService
    ): Response {
        $presupuesto = Presupuesto::where('token', $token)->first();

        if (! $presupuesto) {
            abort(404, 'Presupuesto no encontrado.');
        }

        if ($presupuesto->estado === 'Borrador') {
            abort(403, 'Este presupuesto no está disponible para descarga pública.');
        }

        if ($request->boolean('preview') || $request->boolean('stream')) {
            return $pdfService->stream($presupuesto);
        }

        return $pdfService->descargar($presupuesto);
    }
}
