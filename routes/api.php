<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\PresupuestoController;
use App\Http\Controllers\Api\PresupuestoPublicoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource(
    'clientes',
    ClienteController::class
);

Route::apiResource(
    'productos',
    ProductoController::class
);

Route::apiResource(
    'proveedores',
    ProveedorController::class
)->parameters([
    'proveedores' => 'proveedor',
]);

Route::apiResource(
    'presupuestos',
    PresupuestoController::class
);

Route::get('presupuestos/{presupuesto}/pdf', [PresupuestoController::class, 'pdf']);
Route::post('presupuestos/{presupuesto}/enviar-email', [PresupuestoController::class, 'enviarEmail']);

/*
|--------------------------------------------------------------------------
| Presupuestos Online (Rutas Públicas)
|--------------------------------------------------------------------------
*/
Route::prefix('public/presupuestos')->group(function () {
    Route::get('/{token}', [PresupuestoPublicoController::class, 'show']);
    Route::get('/{token}/pdf', [PresupuestoPublicoController::class, 'descargarPdf']);
    Route::post('/{token}/aceptar', [PresupuestoPublicoController::class, 'aceptar']);
    Route::post('/{token}/rechazar', [PresupuestoPublicoController::class, 'rechazar']);
});