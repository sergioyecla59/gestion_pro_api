<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lineas_presupuesto', function (Blueprint $table) {
            $table->id();

            $table->foreignId('presupuesto_id')
                ->constrained('presupuestos')
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

            $table->index('presupuesto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lineas_presupuesto');
    }
};