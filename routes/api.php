<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\PresupuestoController;
use App\Http\Controllers\Api\PresupuestoPublicoController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\AlbaranController;
use App\Http\Controllers\Api\SerieController;

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
| Pedidos
|--------------------------------------------------------------------------
*/
Route::apiResource(
    'pedidos',
    PedidoController::class
);

Route::patch(
    'pedidos/{pedido}/estado',
    [PedidoController::class, 'cambiarEstado']
);

Route::post(
    'pedidos/{pedido}/anular',
    [PedidoController::class, 'anular']
);

Route::post(
    'presupuestos/{presupuesto}/convertir-en-pedido',
    [PedidoController::class, 'convertirDesdePresupuesto']
);

Route::get(
    'presupuestos/{presupuesto}/pedido',
    [SerieController::class, 'pedidoDePresupuesto']
);

/*
|--------------------------------------------------------------------------
| Albaranes
|--------------------------------------------------------------------------
*/
Route::apiResource(
    'albaranes',
    AlbaranController::class
)->only([
    'index',
    'show',
    'update',
    'destroy',
]);

Route::patch(
    'albaranes/{albaran}/estado',
    [AlbaranController::class, 'cambiarEstado']
);

Route::post(
    'albaranes/{albaran}/anular',
    [AlbaranController::class, 'anular']
);

Route::post(
    'pedidos/{pedido}/crear-albaran',
    [AlbaranController::class, 'crearDesdePedido']
);

/*
|--------------------------------------------------------------------------
| Series y numeración
|--------------------------------------------------------------------------
*/
Route::get(
    'series/previsualizar',
    [SerieController::class, 'previsualizar']
);

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
