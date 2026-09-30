<?php

namespace App\Services;

use App\Models\Serie;

class SerieService
{
    /*
    |--------------------------------------------------------------------------
    | PREFIJOS POR TIPO DE DOCUMENTO
    |--------------------------------------------------------------------------
    |
    | Los números que no estén aquí usan un prefijo
    | genérico. Añade los tipos nuevos aquí y ya
    | funcionan en todos los módulos.
    |
    */
    private const PREFIJOS = [
        'presupuesto' => 'PRE',
        'pedido' => 'PED',
        'albaran' => 'ALB',
        'factura' => 'FAC',
        'compra' => 'COM',
    ];

    /*
    |--------------------------------------------------------------------------
    | SIGUIENTE NÚMERO
    |--------------------------------------------------------------------------
    |
    | Devuelve el siguiente número correlativo del tipo
    | indicado, con el año dentro. Reinicia el contador
    | automáticamente cada 1 de enero.
    |
    | IMPORTANTE: llama a este método SIEMPRE dentro de
    | una transacción (DB::transaction). El lockForUpdate
    | bloquea la fila de la serie hasta que la transacción
    | termina, de modo que dos usuarios que creen un
    | documento a la vez reciben números distintos.
    |
    | Ejemplo de resultado: PED-2026-0001
    |
    */
    public function siguienteNumero(
        int $empresaId,
        string $tipo
    ): string {
        $anio = (int) now()->format('Y');

        $prefijo = self::PREFIJOS[$tipo]
            ?? strtoupper(substr($tipo, 0, 3));

        /*
         * Buscamos o creamos la serie de esta empresa,
         * tipo y año. Si es nueva, ultimo_numero arranca
         * en 0.
         */
        $serie = Serie::where('empresa_id', $empresaId)
            ->where('tipo', $tipo)
            ->where('anio', $anio)
            ->lockForUpdate()
            ->first();

        if (! $serie) {
            $serie = Serie::create([
                'empresa_id' => $empresaId,
                'tipo' => $tipo,
                'prefijo' => $prefijo,
                'anio' => $anio,
                'ultimo_numero' => 0,
            ]);

            /*
             * Volvemos a leer la fila con bloqueo, porque
             * otra petición simultánea podría haberla creado
             * entre el create y ahora.
             */
            $serie = Serie::where('id', $serie->id)
                ->lockForUpdate()
                ->first();
        }

        $siguiente = $serie->ultimo_numero + 1;

        $serie->ultimo_numero = $siguiente;
        $serie->save();

        return sprintf(
            '%s-%d-%04d',
            $serie->prefijo,
            $serie->anio,
            $siguiente
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NÚMERO ACTUAL (SIN CONSUMIR)
    |--------------------------------------------------------------------------
    |
    | Útil para previsualizaciones en el front:
    | muestra qué número saldría sin gastarlo.
    |
    */
    public function previsualizarNumero(
        int $empresaId,
        string $tipo
    ): string {
        $anio = (int) now()->format('Y');

        $prefijo = self::PREFIJOS[$tipo]
            ?? strtoupper(substr($tipo, 0, 3));

        $serie = Serie::where('empresa_id', $empresaId)
            ->where('tipo', $tipo)
            ->where('anio', $anio)
            ->first();

        $siguiente = ($serie?->ultimo_numero ?? 0) + 1;

        return sprintf(
            '%s-%d-%04d',
            $serie?->prefijo ?? $prefijo,
            $anio,
            $siguiente
        );
    }
}
