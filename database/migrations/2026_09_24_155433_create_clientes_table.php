<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            // Empresa a la que pertenece
            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            // Datos principales
            $table->string('nombre');
            $table->string('nif', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 30)->nullable();

            // Dirección
            $table->string('direccion')->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('localidad')->nullable();
            $table->string('provincia')->nullable();
            $table->string('pais')->default('España');

            // Estado
            $table->boolean('activo')->default(true);

            // Información adicional
            $table->text('notas')->nullable();

            $table->timestamps();

            // Índices útiles para búsquedas
            $table->index(['empresa_id', 'nombre']);
            $table->index(['empresa_id', 'nif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};