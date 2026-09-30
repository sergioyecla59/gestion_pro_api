<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añadir los campos de fecha que registran
     * CUÁNDO ocurrió cada transición automática.
     * Sin esto, un pedido completado no tiene
     * trazabilidad de por qué lo está.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->timestamp('fecha_envio')
                ->nullable()
                ->after('fecha_entrega');

            $table->timestamp('fecha_completado')
                ->nullable()
                ->after('fecha_envio');

            $table->timestamp('fecha_cancelacion')
                ->nullable()
                ->after('fecha_completado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_envio',
                'fecha_completado',
                'fecha_cancelacion',
            ]);
        });
    }
};
