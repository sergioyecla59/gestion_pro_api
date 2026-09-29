<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->string('referencia', 100);
            $table->string('nombre');
            $table->string('categoria')->nullable();

            $table->decimal('precio', 10, 2)->default(0);

            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(0);

            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->unique([
                'empresa_id',
                'referencia',
            ]);

            $table->index([
                'empresa_id',
                'nombre',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};