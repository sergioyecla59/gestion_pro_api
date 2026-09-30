<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crear la tabla lineas_pedido.
     */
    public function up(): void
    {
        Schema::create('lineas_pedido', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pedido_id')
                ->constrained('pedidos')
                ->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->nullable()
                ->constrained('productos')
                ->nullOnDelete();

            $table->string('descripcion');

            $table->decimal('cantidad', 10, 2)->default(1);

            $table->decimal('precio_unitario', 12, 2)->default(0);

            $table->decimal('porcentaje_iva', 5, 2)->default(21);

            $table->decimal('subtotal', 12, 2)->default(0);

            $table->timestamps();

            $table->index('pedido_id');
        });
    }

    /**
     * Eliminar la tabla lineas_pedido.
     */
    public function down(): void
    {
        Schema::dropIfExists('lineas_pedido');
    }
};
