<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añadir los campos de anulación.
     *
     * Un documento no se borra: se anula y queda el
     * rastro de cuándo y por qué.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->timestamp('fecha_anulacion')
                ->nullable()
                ->after('fecha_cancelacion');

            $table->text('motivo_anulacion')
                ->nullable()
                ->after('fecha_anulacion');
        });

        Schema::table('albaranes', function (Blueprint $table) {
            $table->timestamp('fecha_anulacion')
                ->nullable()
                ->after('observaciones');

            $table->text('motivo_anulacion')
                ->nullable()
                ->after('fecha_anulacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_anulacion',
                'motivo_anulacion',
            ]);
        });

        Schema::table('albaranes', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_anulacion',
                'motivo_anulacion',
            ]);
        });
    }
};
