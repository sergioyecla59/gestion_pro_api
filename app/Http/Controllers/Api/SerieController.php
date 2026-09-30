<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Presupuesto;
use App\Services\SerieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SerieController extends Controller
{
    public function __construct(
        private SerieService $series
    ) {}

    /*
    |--------------------------------------------------------------------------
    | PREVISUALIZAR NÚMEROS
    |--------------------------------------------------------------------------
    |
    | Devuelve el número que saldría para cada tipo de
    | documento SIN consumirlo. Sirve para que el front
    | pueda mostrar "se creará como PED-2026-0004" antes
    | de guardar nada.
    |
    | GET /api/series/previsualizar?empresa_id=1
    |
    */
    public function previsualizar(
        Request $request
    ): JsonResponse {
        $datos = $request->validate([
            'empresa_id' => [
                'required',
                'exists:empresas,id',
            ],
        ]);

        $empresaId = (int) $datos['empresa_id'];

        return response()->json([
            'presupuesto' => $this->series->previsualizarNumero(
                $empresaId,
                'presupuesto'
            ),
            'pedido' => $this->series->previsualizarNumero(
                $empresaId,
                'pedido'
            ),
            'albaran' => $this->series->previsualizarNumero(
                $empresaId,
                'albaran'
            ),
            'factura' => $this->series->previsualizarNumero(
                $empresaId,
                'factura'
            ),
            'compra' => $this->series->previsualizarNumero(
                $empresaId,
                'compra'
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICAR SI UN PRESUPUESTO YA ES PEDIDO
    |--------------------------------------------------------------------------
    |
    | El panel de presupuestos lo usa para saber si debe
    | mostrar el botón o el aviso de "ya convertido".
    |
    | GET /api/presupuestos/{presupuesto}/pedido
    |
    */
    public function pedidoDePresupuesto(
        Presupuesto $presupuesto
    ): JsonResponse {
        $pedido = Pedido::where(
            'presupuesto_id',
            $presupuesto->id
        )->first();

        return response()->json([
            'convertido' => $pedido !== null,
            'pedido' => $pedido,
        ]);
    }
}
