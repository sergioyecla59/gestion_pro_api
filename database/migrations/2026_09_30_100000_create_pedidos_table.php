<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crear la tabla pedidos.
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->restrictOnDelete();

            // Pedido que nace de un presupuesto aceptado.
            // Si el pedido se crea a mano, queda a null.
            $table->foreignId('presupuesto_id')
                ->nullable()
                ->constrained('presupuestos')
                ->nullOnDelete();

            $table->string('numero', 50);

            $table->date('fecha');
            $table->date('fecha_entrega')->nullable();

            // Origen del pedido: "Pedido manual",
            // "Presupuesto P-2026-001", etc.
            $table->string('origen', 120)->nullable();

            $table->string('estado', 30)->default('Pendiente');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->text('notas')->nullable();

            $table->timestamps();

            $table->unique(['empresa_id', 'numero']);
            $table->index(['empresa_id', 'fecha']);
            $table->index(['empresa_id', 'estado']);
        });
    }

    /**
     * Eliminar la tabla pedidos.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
