<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Albaran;
use App\Models\Pedido;
use App\Services\FlujoService;
use App\Services\SerieService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AlbaranController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ESTADOS VÁLIDOS
    |--------------------------------------------------------------------------
    */
    private const ESTADOS = [
        'Pendiente',
        'Preparado',
        'Entregado',
    ];

    /*
    |--------------------------------------------------------------------------
    | ESTADOS DEL PEDIDO QUE PERMITEN GENERAR ALBARÁN
    |--------------------------------------------------------------------------
    */
    private const ESTADOS_PEDIDO_VALIDOS = [
        'Preparando',
        'Enviado',
        'Completado',
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
        $albaranes = Albaran::with([
            'cliente',
            'pedido',
            'lineas.producto',
        ])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        return response()->json($albaranes);
    }

    /*
    |--------------------------------------------------------------------------
    | VER
    |--------------------------------------------------------------------------
    */
    public function show(Albaran $albaran)
    {
        $albaran->load([
            'cliente',
            'pedido',
            'lineas.producto',
        ]);

        return response()->json($albaran);
    }

    /*
    |--------------------------------------------------------------------------
    | CREAR DESDE PEDIDO
    |--------------------------------------------------------------------------
    */
    public function crearDesdePedido(
        Request $request,
        Pedido $pedido
    ) {
        if (
            ! in_array(
                $pedido->estado,
                self::ESTADOS_PEDIDO_VALIDOS,
                true
            )
        ) {
            return response()->json([
                'message' => 'No se puede crear un albarán de un pedido en estado ' . $pedido->estado . '.',
                'estado_actual' => $pedido->estado,
                'estados_validos' => self::ESTADOS_PEDIDO_VALIDOS,
            ], 422);
        }

        $yaExiste = Albaran::where(
            'pedido_id',
            $pedido->id
        )->exists();

        if ($yaExiste) {
            return response()->json([
                'message' => 'Este pedido ya tiene un albarán asociado.',
            ], 422);
        }

        $datos = $request->validate([
            'fecha' => [
                'nullable',
                'date',
            ],
            'observaciones' => [
                'nullable',
                'string',
            ],
        ]);

        $pedido->loadMissing('lineas');

        if ($pedido->lineas->isEmpty()) {
            return response()->json([
                'message' => 'El pedido no tiene líneas: no se puede generar un albarán vacío.',
            ], 422);
        }

        $albaran = DB::transaction(function () use (
            $pedido,
            $datos
        ) {

            $numero = $this->series->siguienteNumero(
                (int) $pedido->empresa_id,
                'albaran'
            );

            $lineas = $pedido->lineas
                ->map(fn ($linea) => [
                    'producto_id' => $linea->producto_id,
                    'descripcion' => $linea->descripcion,
                    'cantidad' => $linea->cantidad,
                    'precio_unitario' => $linea->precio_unitario,
                    'porcentaje_iva' => $linea->porcentaje_iva,
                    'subtotal' => $linea->subtotal,
                ])
                ->all();

            $albaran = Albaran::create([
                'empresa_id' => $pedido->empresa_id,
                'cliente_id' => $pedido->cliente_id,
                'pedido_id' => $pedido->id,
                'numero' => $numero,
                'fecha' => $datos['fecha']
                    ?? now()->toDateString(),
                'estado' => 'Pendiente',
                'subtotal' => $pedido->subtotal,
                'iva' => $pedido->iva,
                'total' => $pedido->total,
                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            foreach ($lineas as $linea) {
                $albaran->lineas()->create($linea);
            }

            /*
             * CONSECUENCIA AUTOMÁTICA:
             * si ya hay albarán, el pedido está en camino.
             */
            $this->flujo->pedidoEnviado($pedido);

            return $albaran;
        });

        $albaran->load([
            'cliente',
            'pedido',
            'lineas.producto',
        ]);

        return response()->json(
            $albaran,
            201
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CAMBIAR ESTADO
    |--------------------------------------------------------------------------
    */
    public function cambiarEstado(
        Request $request,
        Albaran $albaran
    ) {
        if ($albaran->estado === 'Anulado') {
            return response()->json([
                'message' => 'Este albarán está anulado y no admite cambios de estado.',
            ], 422);
        }

        $datos = $request->validate([
            'estado' => [
                'required',
                Rule::in(self::ESTADOS),
            ],
        ]);

        $albaran->loadMissing('pedido');

        $albaran = DB::transaction(function () use (
            $datos,
            $albaran
        ) {

            $albaran->estado = $datos['estado'];

            if ($datos['estado'] === 'Entregado') {
                $albaran->fecha_entrega = $albaran->fecha_entrega
                    ?? now();
            }

            $albaran->save();

            /*
             * CONSECUENCIA AUTOMÁTICA:
             * mercancía entregada → pedido completado.
             */
            if (
                $datos['estado'] === 'Entregado'
                && $albaran->pedido
            ) {
                $this->flujo->pedidoEntregado(
                    $albaran->pedido
                );
            }

            return $albaran;
        });

        $albaran->load([
            'cliente',
            'pedido',
            'lineas.producto',
        ]);

        return response()->json($albaran);
    }

    /*
    |--------------------------------------------------------------------------
    | EDITAR
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        Albaran $albaran
    ) {
        $datos = $request->validate([
            'observaciones' => [
                'nullable',
                'string',
            ],
        ]);

        $albaran->update([
            'observaciones' => $datos['observaciones'] ?? null,
        ]);

        return response()->json($albaran);
    }

    /*
    |--------------------------------------------------------------------------
    | ANULAR
    |--------------------------------------------------------------------------
    |
    | Un albarán entregado NO se borra: se anula.
    | Queda registrado cuándo y por qué, y el pedido
    | asociado vuelve a estar libre para gestionarse.
    |
    */
    public function anular(
        Request $request,
        Albaran $albaran
    ) {
        if ($albaran->estado === 'Anulado') {
            return response()->json([
                'message' => 'Este albarán ya está anulado.',
            ], 422);
        }

        $datos = $request->validate([
            'motivo' => [
                'required',
                'string',
                'max:500',
            ],
        ]);

        $albaran->loadMissing('pedido');

        DB::transaction(function () use (
            $datos,
            $albaran
        ) {
            $albaran->estado = 'Anulado';
            $albaran->fecha_anulacion = now();
            $albaran->motivo_anulacion = $datos['motivo'];
            $albaran->save();

            /*
             * El pedido vuelve a estar pendiente: si el
             * albarán se anuló, la entrega ya no existe.
             */
            if ($albaran->pedido) {
                $pedido = $albaran->pedido;

                if ($pedido->estado !== 'Anulado') {
                    $pedido->estado = 'Preparando';
                    $pedido->fecha_envio = null;
                    $pedido->fecha_completado = null;
                    $pedido->save();
                }
            }
        });

        $albaran->load([
            'cliente',
            'pedido',
            'lineas.producto',
        ]);

        return response()->json($albaran);
    }

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR
    |--------------------------------------------------------------------------
    |
    | Solo albaranes que todavía no se han entregado.
    | Los entregados se anulan.
    |
    */
    public function destroy(Albaran $albaran)
    {
        if ($albaran->estado === 'Entregado') {
            return response()->json([
                'message' => 'No se puede eliminar el albarán ' . $albaran->numero . ' porque ya consta como entregado. En su lugar, anúlalo para dejar constancia.',
            ], 422);
        }

        if ($albaran->estado === 'Anulado') {
            return response()->json([
                'message' => 'No se puede eliminar un albarán anulado: forma parte del histórico.',
            ], 422);
        }

        $albaran->delete();

        return response()->json([
            'message' =>
                'Albarán eliminado correctamente.',
        ]);
    }
}
