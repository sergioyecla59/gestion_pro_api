<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->string('nombre');
            $table->string('nif', 20)->nullable();

            $table->string('telefono', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('contacto')->nullable();

            $table->string('direccion')->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('poblacion')->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->index(['empresa_id', 'nombre']);
            $table->index(['empresa_id', 'nif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};