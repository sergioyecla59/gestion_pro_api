<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crear la tabla albaranes.
     *
     * Un albarán nace SIEMPRE de un pedido (pedido_id
     * obligatorio), así que la trazabilidad queda
     * cerrada: presupuesto → pedido → albarán.
     */
    public function up(): void
    {
        Schema::create('albaranes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->restrictOnDelete();

            $table->foreignId('pedido_id')
                ->constrained('pedidos')
                ->restrictOnDelete();

            $table->string('numero', 50);

            $table->date('fecha');

            // Fecha real de entrega, se rellena al
            // marcar el albarán como Entregado.
            $table->timestamp('fecha_entrega')->nullable();

            $table->string('estado', 30)->default('Pendiente');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->unique(['empresa_id', 'numero']);

            // Un pedido no puede tener dos albaranes.
            $table->unique('pedido_id');

            $table->index(['empresa_id', 'fecha']);
            $table->index(['empresa_id', 'estado']);
        });
    }

    /**
     * Eliminar la tabla albaranes.
     */
    public function down(): void
    {
        Schema::dropIfExists('albaranes');
    }
};
