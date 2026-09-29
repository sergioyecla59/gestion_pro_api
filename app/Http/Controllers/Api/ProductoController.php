<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAR PRODUCTOS
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $productos = Producto::orderBy('nombre')->get();

        return response()->json($productos);
    }

    /*
    |--------------------------------------------------------------------------
    | CREAR PRODUCTO
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $datos = $request->validate([
            'empresa_id' => [
                'required',
                'exists:empresas,id',
            ],

            'referencia' => [
                'required',
                'string',
                'max:100',

                Rule::unique('productos', 'referencia')
                    ->where(
                        fn ($query) =>
                        $query->where(
                            'empresa_id',
                            $request->empresa_id
                        )
                    ),
            ],

            'nombre' => [
                'required',
                'string',
                'max:255',
            ],

            'categoria' => [
                'nullable',
                'string',
                'max:255',
            ],

            'precio' => [
                'required',
                'numeric',
                'min:0',
            ],

            'stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'stock_minimo' => [
                'required',
                'integer',
                'min:0',
            ],

            'activo' => [
                'boolean',
            ],
        ]);

        $producto = Producto::create($datos);

        return response()->json(
            $producto,
            201
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VER PRODUCTO
    |--------------------------------------------------------------------------
    */

    public function show(Producto $producto)
    {
        return response()->json($producto);
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR PRODUCTO
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Producto $producto
    ) {
        $datos = $request->validate([
            'referencia' => [
                'sometimes',
                'required',
                'string',
                'max:100',

                Rule::unique(
                    'productos',
                    'referencia'
                )
                    ->where(
                        fn ($query) =>
                        $query->where(
                            'empresa_id',
                            $producto->empresa_id
                        )
                    )
                    ->ignore($producto->id),
            ],

            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'categoria' => [
                'nullable',
                'string',
                'max:255',
            ],

            'precio' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],

            'stock' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
            ],

            'stock_minimo' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
            ],

            'activo' => [
                'boolean',
            ],
        ]);

        $producto->update($datos);

        return response()->json($producto);
    }

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR PRODUCTO
    |--------------------------------------------------------------------------
    */

    public function destroy(Producto $producto)
    {
        $producto->delete();

        return response()->json([
            'message' =>
                'Producto eliminado correctamente.',
        ]);
    }
}