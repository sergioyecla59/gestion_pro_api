<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PresupuestoMail;
use App\Models\Presupuesto;
use App\Services\PresupuestoPdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class PresupuestoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAR
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $presupuestos = Presupuesto::with([
            'cliente',
            'lineas.producto',
        ])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        return response()->json($presupuestos);
    }

    /*
    |--------------------------------------------------------------------------
    | CREAR
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $datos = $this->validarPresupuesto($request);

        $presupuesto = DB::transaction(function () use ($datos) {

            [$baseImponible, $totalIva, $total] =
                $this->calcularTotales($datos['lineas']);

            $presupuesto = Presupuesto::create([
                'empresa_id' => $datos['empresa_id'],
                'cliente_id' => $datos['cliente_id'],
                'numero' => $datos['numero'],
                'fecha' => $datos['fecha'],
                'valido_hasta' => $datos['valido_hasta'] ?? null,
                'estado' => $datos['estado'],
                'base_imponible' => $baseImponible,
                'iva' => $totalIva,
                'total' => $total,
                'notas' => $datos['notas'] ?? null,
            ]);

            $this->crearLineas(
                $presupuesto,
                $datos['lineas']
            );

            return $presupuesto;
        });

        $presupuesto->load([
            'cliente',
            'lineas.producto',
        ]);

        return response()->json(
            $presupuesto,
            201
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VER
    |--------------------------------------------------------------------------
    */
    public function show(Presupuesto $presupuesto)
    {
        $presupuesto->load([
            'cliente',
            'lineas.producto',
        ]);

        return response()->json($presupuesto);
    }

    /*
    |--------------------------------------------------------------------------
    | EDITAR COMPLETO
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        Presupuesto $presupuesto
    ) {
        $datos = $this->validarPresupuesto(
            $request,
            $presupuesto
        );

        DB::transaction(function () use (
            $datos,
            $presupuesto
        ) {
            [$baseImponible, $totalIva, $total] =
                $this->calcularTotales($datos['lineas']);

            $presupuesto->update([
                'empresa_id' => $datos['empresa_id'],
                'cliente_id' => $datos['cliente_id'],
                'numero' => $datos['numero'],
                'fecha' => $datos['fecha'],
                'valido_hasta' => $datos['valido_hasta'] ?? null,
                'estado' => $datos['estado'],
                'base_imponible' => $baseImponible,
                'iva' => $totalIva,
                'total' => $total,
                'notas' => $datos['notas'] ?? null,
            ]);

            /*
             * Para esta primera versión:
             * eliminamos las líneas antiguas y
             * creamos las nuevas.
             */
            $presupuesto->lineas()->delete();

            $this->crearLineas(
                $presupuesto,
                $datos['lineas']
            );
        });

        $presupuesto->load([
            'cliente',
            'lineas.producto',
        ]);

        return response()->json($presupuesto);
    }

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR
    |--------------------------------------------------------------------------
    */
    public function destroy(Presupuesto $presupuesto)
    {
        $presupuesto->delete();

        return response()->json([
            'message' =>
                'Presupuesto eliminado correctamente.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDACIÓN
    |--------------------------------------------------------------------------
    */
    private function validarPresupuesto(
        Request $request,
        ?Presupuesto $presupuesto = null
    ): array {
        $reglaNumero = Rule::unique(
            'presupuestos',
            'numero'
        )->where(
            fn ($query) =>
                $query->where(
                    'empresa_id',
                    $request->empresa_id
                )
        );

        if ($presupuesto) {
            $reglaNumero->ignore($presupuesto->id);
        }

        return $request->validate([
            'empresa_id' => [
                'required',
                'exists:empresas,id',
            ],

            'cliente_id' => [
                'required',
                'exists:clientes,id',
            ],

            'numero' => [
                'required',
                'string',
                'max:50',
                $reglaNumero,
            ],

            'fecha' => [
                'required',
                'date',
            ],

            'valido_hasta' => [
                'nullable',
                'date',
                'after_or_equal:fecha',
            ],

            'estado' => [
                'required',
                Rule::in([
                    'Borrador',
                    'Pendiente',
                    'Aceptado',
                    'Rechazado',
                ]),
            ],

            'notas' => [
                'nullable',
                'string',
            ],

            'lineas' => [
                'required',
                'array',
                'min:1',
            ],

            'lineas.*.producto_id' => [
                'nullable',
                'exists:productos,id',
            ],

            'lineas.*.descripcion' => [
                'required',
                'string',
                'max:255',
            ],

            'lineas.*.cantidad' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'lineas.*.precio_unitario' => [
                'required',
                'numeric',
                'min:0',
            ],

            'lineas.*.porcentaje_iva' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULAR TOTALES
    |--------------------------------------------------------------------------
    */
    private function calcularTotales(
        array $lineas
    ): array {
        $baseImponible = 0;
        $totalIva = 0;

        foreach ($lineas as $linea) {
            $subtotal =
                $linea['cantidad']
                * $linea['precio_unitario'];

            $importeIva =
                $subtotal
                * ($linea['porcentaje_iva'] / 100);

            $baseImponible += $subtotal;
            $totalIva += $importeIva;
        }

        $baseImponible =
            round($baseImponible, 2);

        $totalIva =
            round($totalIva, 2);

        $total =
            round(
                $baseImponible + $totalIva,
                2
            );

        return [
            $baseImponible,
            $totalIva,
            $total,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CREAR LÍNEAS
    |--------------------------------------------------------------------------
    */
    private function crearLineas(
        Presupuesto $presupuesto,
        array $lineas
    ): void {
        foreach ($lineas as $linea) {
            $subtotal = round(
                $linea['cantidad']
                * $linea['precio_unitario'],
                2
            );

            $presupuesto->lineas()->create([
                'producto_id' =>
                    $linea['producto_id'] ?? null,

                'descripcion' =>
                    $linea['descripcion'],

                'cantidad' =>
                    $linea['cantidad'],

                'precio_unitario' =>
                    $linea['precio_unitario'],

                'porcentaje_iva' =>
                    $linea['porcentaje_iva'],

                'subtotal' =>
                    $subtotal,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GENERAR / DESCARGAR PDF
    |--------------------------------------------------------------------------
    */
    public function pdf(
        Request $request,
        Presupuesto $presupuesto,
        PresupuestoPdfService $pdfService
    ): Response {
        if ($request->boolean('preview') || $request->boolean('stream')) {
            return $pdfService->stream($presupuesto);
        }

        return $pdfService->descargar($presupuesto);
    }

    /*
    |--------------------------------------------------------------------------
    | ENVIAR POR EMAIL AL CLIENTE
    |--------------------------------------------------------------------------
    */
    public function enviarEmail(
        Request $request,
        Presupuesto $presupuesto,
        PresupuestoPdfService $pdfService
    ): JsonResponse {
        // Regla estricta: NO permitir envío si está en estado Borrador
        if ($presupuesto->estado === 'Borrador') {
            return response()->json([
                'message' => 'No se puede enviar por email un presupuesto en estado Borrador. Debe cambiar su estado a Pendiente antes de enviarlo.',
            ], 422);
        }

        $datos = $request->validate([
            'email_destinatario' => ['nullable', 'email'],
            'asunto' => ['nullable', 'string', 'max:255'],
            'mensaje' => ['nullable', 'string', 'max:2000'],
            'adjuntar_pdf' => ['nullable', 'boolean'],
        ]);

        $presupuesto->loadMissing([
            'empresa',
            'cliente',
            'lineas.producto',
        ]);

        $emailDestinatario = $datos['email_destinatario'] ?? $presupuesto->cliente?->email;

        if (! $emailDestinatario) {
            return response()->json([
                'message' => 'No se especificó un email destinatario y el cliente asociado no tiene email registrado.',
            ], 422);
        }

        $adjuntarPdf = $request->has('adjuntar_pdf')
            ? $request->boolean('adjuntar_pdf')
            : true;

        $pdfBinario = $adjuntarPdf
            ? $pdfService->obtenerContenidoBinario($presupuesto)
            : null;

        Mail::to($emailDestinatario)->send(
            new PresupuestoMail(
                presupuesto: $presupuesto,
                mensajePersonalizado: $datos['mensaje'] ?? null,
                adjuntarPdf: $adjuntarPdf,
                pdfBinario: $pdfBinario,
                asunto: $datos['asunto'] ?? null
            )
        );

        return response()->json([
            'message' => 'Presupuesto enviado correctamente por correo electrónico.',
            'destinatario' => $emailDestinatario,
            'adjunto_pdf' => $adjuntarPdf,
            'presupuesto_numero' => $presupuesto->numero,
        ]);
    }
}