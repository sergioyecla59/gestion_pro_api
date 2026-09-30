<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crear la tabla series.
     *
     * Una fila por empresa + tipo de documento + año.
     * El campo ultimo_numero guarda el último número
     * emitido y se incrementa con bloqueo de fila para
     * que nunca se repita ni se salte ninguno.
     */
    public function up(): void
    {
        Schema::create('series', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            // 'presupuesto', 'pedido', 'albaran',
            // 'factura', 'compra'...
            $table->string('tipo', 30);

            // 'PRE', 'PED', 'ALB', 'FAC'...
            $table->string('prefijo', 10);

            $table->year('anio');

            $table->unsignedInteger('ultimo_numero')->default(0);

            $table->timestamps();

            $table->unique([
                'empresa_id',
                'tipo',
                'anio',
            ]);

            $table->index([
                'empresa_id',
                'tipo',
            ]);
        });
    }

    /**
     * Eliminar la tabla series.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
