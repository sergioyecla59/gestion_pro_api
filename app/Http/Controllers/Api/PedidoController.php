<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Presupuesto;
use App\Services\FlujoService;
use App\Services\SerieService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PedidoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ESTADOS VÁLIDOS
    |--------------------------------------------------------------------------
    */
    private const ESTADOS = [
        'Pendiente',
        'Preparando',
        'Enviado',
        'Completado',
        'Anulado',
    ];

    public function __construct(
        private SerieService $series,
        private FlujoService $flujo
    ) {}

    /*
    |--------------------------------------------------------------------------
    | LISTAR
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $pedidos = Pedido::with([
            'cliente',
            'presupuesto',
            'albaran',
            'lineas.producto',
        ])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        return response()->json($pedidos);
    }

    /*
    |--------------------------------------------------------------------------
    | CREAR
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $datos = $this->validarPedido($request, ignorarNumero: true);

        $pedido = DB::transaction(function () use ($datos) {

            $numero = $this->series->siguienteNumero(
                (int) $datos['empresa_id'],
                'pedido'
            );

            [$subtotal, $totalIva, $total] =
                $this->calcularTotales($datos['lineas']);

            $pedido = Pedido::create([
                'empresa_id' => $datos['empresa_id'],
                'cliente_id' => $datos['cliente_id'],
                'presupuesto_id' => $datos['presupuesto_id'] ?? null,
                'numero' => $numero,
                'fecha' => $datos['fecha'],
                'fecha_entrega' => $datos['fecha_entrega'] ?? null,
                'origen' => $datos['origen'] ?? 'Pedido manual',
                'estado' => $datos['estado'],
                'subtotal' => $subtotal,
                'iva' => $totalIva,
                'total' => $total,
                'notas' => $datos['notas'] ?? null,
            ]);

            $this->crearLineas(
                $pedido,
                $datos['lineas']
            );

            return $pedido;
        });

        $pedido->load([
            'cliente',
            'presupuesto',
            'albaran',
            'lineas.producto',
        ]);

        return response()->json(
            $pedido,
            201
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VER
    |--------------------------------------------------------------------------
    */
    public function show(Pedido $pedido)
    {
        $pedido->load([
            'cliente',
            'presupuesto',
            'albaran',
            'lineas.producto',
        ]);

        return response()->json($pedido);
    }

    /*
    |--------------------------------------------------------------------------
    | EDITAR
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        Pedido $pedido
    ) {
        $datos = $this->validarPedido(
            $request,
            $pedido,
            ignorarNumero: true
        );

        DB::transaction(function () use (
            $datos,
            $pedido
        ) {
            [$subtotal, $totalIva, $total] =
                $this->calcularTotales($datos['lineas']);

            $pedido->update([
                'empresa_id' => $datos['empresa_id'],
                'cliente_id' => $datos['cliente_id'],
                'presupuesto_id' => $datos['presupuesto_id'] ?? null,
                'fecha' => $datos['fecha'],
                'fecha_entrega' => $datos['fecha_entrega'] ?? null,
                'origen' => $datos['origen'] ?? $pedido->origen,
                'subtotal' => $subtotal,
                'iva' => $totalIva,
                'total' => $total,
                'notas' => $datos['notas'] ?? null,
            ]);

            $pedido->lineas()->delete();

            $this->crearLineas(
                $pedido,
                $datos['lineas']
            );
        });

        $pedido->load([
            'cliente',
            'presupuesto',
            'albaran',
            'lineas.producto',
        ]);

        return response()->json($pedido);
    }

    /*
    |--------------------------------------------------------------------------
    | CAMBIAR ESTADO
    |--------------------------------------------------------------------------
    |
    | Solo estados manuales, y nunca retroceder
    | si el pedido ya generó su albarán.
    |
    */
    public function cambiarEstado(
        Request $request,
        Pedido $pedido
    ) {
        $datos = $request->validate([
            'estado' => [
                'required',
                Rule::in(self::ESTADOS),
            ],
            'motivo' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $motivo = $this->flujo->motivoRechazo(
            $pedido,
            $datos['estado']
        );

        if ($motivo) {
            return response()->json([
                'message' => $motivo,
                'estado_actual' => $pedido->estado,
                'estados_manuales' => FlujoService::ESTADOS_MANUALES,
            ], 422);
        }

        if ($datos['estado'] === 'Anulado') {
            $pedido->fecha_anulacion = now();
            $pedido->motivo_anulacion = $datos['motivo'] ?? null;
        }

        $pedido->estado = $datos['estado'];
        $pedido->save();

        $pedido->load([
            'cliente',
            'presupuesto',
            'albaran',
        ]);

        return response()->json($pedido);
    }

    /*
    |--------------------------------------------------------------------------
    | ANULAR
    |--------------------------------------------------------------------------
    |
    | NO se borra: se anula y queda el rastro.
    | Un pedido con albarán entregado se puede anular
    | (la mercancía salió y hay que deshacerlo con
    | registro), pero nunca borrar.
    |
    */
    public function anular(
        Request $request,
        Pedido $pedido
    ) {
        if ($pedido->estado === 'Anulado') {
            return response()->json([
                'message' => 'Este pedido ya está anulado.',
            ], 422);
        }

        $datos = $request->validate([
            'motivo' => [
                'required',
                'string',
                'max:500',
            ],
        ]);

        $pedido->estado = 'Anulado';
        $pedido->fecha_anulacion = now();
        $pedido->motivo_anulacion = $datos['motivo'];
        $pedido->save();

        $pedido->load([
            'cliente',
            'presupuesto',
            'albaran',
        ]);

        return response()->json($pedido);
    }

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR
    |--------------------------------------------------------------------------
    |
    | Solo se permite si el pedido no ha generado
    | albarán ni está completado. Si ya salió de la
    | empresa, la vía es anularlo.
    |
    */
    public function destroy(Pedido $pedido)
    {
        if ($pedido->albaran()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el pedido ' . $pedido->numero . ' porque ya tiene un albarán generado. En su lugar, anúlalo para dejar constancia.',
            ], 422);
        }

        $pedido->delete();

        return response()->json([
            'message' =>
                'Pedido eliminado correctamente.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CONVERTIR PRESUPUESTO EN PEDIDO
    |--------------------------------------------------------------------------
    */
    public function convertirDesdePresupuesto(
        Request $request,
        Presupuesto $presupuesto
    ) {
        if ($presupuesto->estado !== 'Aceptado') {
            return response()->json([
                'message' => 'Solo se pueden convertir en pedido los presupuestos en estado Aceptado.',
                'estado_actual' => $presupuesto->estado,
            ], 422);
        }

        $yaExiste = Pedido::where(
            'presupuesto_id',
            $presupuesto->id
        )->exists();

        if ($yaExiste) {
            return response()->json([
                'message' => 'Este presupuesto ya tiene un pedido asociado.',
            ], 422);
        }

        $datos = $request->validate([
            'fecha' => [
                'nullable',
                'date',
            ],
            'fecha_entrega' => [
                'nullable',
                'date',
            ],
            'estado' => [
                'nullable',
                Rule::in(self::ESTADOS),
            ],
            'notas' => [
                'nullable',
                'string',
            ],
        ]);

        $presupuesto->loadMissing('lineas');

        $pedido = DB::transaction(function () use (
            $presupuesto,
            $datos
        ) {

            $numero = $this->series->siguienteNumero(
                (int) $presupuesto->empresa_id,
                'pedido'
            );

            $lineas = $presupuesto->lineas
                ->map(fn ($linea) => [
                    'producto_id' => $linea->producto_id,
                    'descripcion' => $linea->descripcion,
                    'cantidad' => $linea->cantidad,
                    'precio_unitario' => $linea->precio_unitario,
                    'porcentaje_iva' => $linea->porcentaje_iva,
                ])
                ->all();

            [$subtotal, $totalIva, $total] =
                $this->calcularTotales($lineas);

            $pedido = Pedido::create([
                'empresa_id' => $presupuesto->empresa_id,
                'cliente_id' => $presupuesto->cliente_id,
                'presupuesto_id' => $presupuesto->id,
                'numero' => $numero,
                'fecha' => $datos['fecha']
                    ?? now()->toDateString(),
                'fecha_entrega' => $datos['fecha_entrega'] ?? null,
                'origen' => 'Presupuesto ' . $presupuesto->numero,
                'estado' => 'Pendiente',
                'subtotal' => $subtotal,
                'iva' => $totalIva,
                'total' => $total,
                'notas' => $datos['notas']
                    ?? $presupuesto->notas,
            ]);

            $this->crearLineas(
                $pedido,
                $lineas
            );

            return $pedido;
        });

        $pedido->load([
            'cliente',
            'presupuesto',
            'albaran',
            'lineas.producto',
        ]);

        return response()->json(
            $pedido,
            201
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDACIÓN
    |--------------------------------------------------------------------------
    */
    private function validarPedido(
        Request $request,
        ?Pedido $pedido = null,
        bool $ignorarNumero = false
    ): array {
        $reglas = [
            'empresa_id' => [
                'required',
                'exists:empresas,id',
            ],

            'cliente_id' => [
                'required',
                'exists:clientes,id',
            ],

            'presupuesto_id' => [
                'nullable',
                'exists:presupuestos,id',
            ],

            'fecha' => [
                'required',
                'date',
            ],

            'fecha_entrega' => [
                'nullable',
                'date',
                'after_or_equal:fecha',
            ],

            'origen' => [
                'nullable',
                'string',
                'max:120',
            ],

            'estado' => [
                'required',
                Rule::in(self::ESTADOS),
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
        ];

        if (! $ignorarNumero) {
            $reglaNumero = Rule::unique(
                'pedidos',
                'numero'
            )->where(
                fn ($query) =>
                    $query->where(
                        'empresa_id',
                        $request->empresa_id
                    )
            );

            if ($pedido) {
                $reglaNumero->ignore($pedido->id);
            }

            $reglas['numero'] = [
                'required',
                'string',
                'max:50',
                $reglaNumero,
            ];
        }

        return $request->validate($reglas);
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULAR TOTALES
    |--------------------------------------------------------------------------
    */
    private function calcularTotales(
        array $lineas
    ): array {
        $subtotal = 0;
        $totalIva = 0;

        foreach ($lineas as $linea) {
            $importeLinea =
                $linea['cantidad']
                * $linea['precio_unitario'];

            $importeIva =
                $importeLinea
                * ($linea['porcentaje_iva'] / 100);

            $subtotal += $importeLinea;
            $totalIva += $importeIva;
        }

        $subtotal =
            round($subtotal, 2);

        $totalIva =
            round($totalIva, 2);

        $total =
            round(
                $subtotal + $totalIva,
                2
            );

        return [
            $subtotal,
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
        Pedido $pedido,
        array $lineas
    ): void {
        foreach ($lineas as $linea) {
            $subtotal = round(
                $linea['cantidad']
                * $linea['precio_unitario'],
                2
            );

            $pedido->lineas()->create([
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
}
