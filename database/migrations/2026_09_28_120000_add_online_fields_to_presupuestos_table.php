<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            $table->string('token', 64)
                ->nullable()
                ->unique()
                ->after('notas');

            $table->timestamp('fecha_aceptacion')
                ->nullable()
                ->after('token');

            $table->timestamp('fecha_rechazo')
                ->nullable()
                ->after('fecha_aceptacion');

            $table->text('motivo_rechazo')
                ->nullable()
                ->after('fecha_rechazo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            $table->dropColumn([
                'token',
                'fecha_aceptacion',
                'fecha_rechazo',
                'motivo_rechazo',
            ]);
        });
    }
};
