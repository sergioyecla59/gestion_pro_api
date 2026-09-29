<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crear la tabla empresas.
     */
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();

            // Datos principales
            $table->string('nombre_comercial');
            $table->string('razon_social')->nullable();
            $table->string('nif', 20)->nullable();

            // Datos de contacto
            $table->string('email')->nullable();
            $table->string('telefono', 30)->nullable();

            // Dirección
            $table->string('direccion')->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('localidad')->nullable();
            $table->string('provincia')->nullable();
            $table->string('pais')->default('España');

            // Estado
            $table->boolean('activa')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Eliminar la tabla empresas.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};