<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::orderBy('nombre')->get();

        return response()->json($proveedores);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'empresa_id' => [
                'required',
                'exists:empresas,id',
            ],
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],
            'nif' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('proveedores', 'nif')
                    ->where(
                        fn ($query) =>
                        $query->where(
                            'empresa_id',
                            $request->empresa_id
                        )
                    ),
            ],
            'telefono' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'contacto' => [
                'nullable',
                'string',
                'max:255',
            ],
            'direccion' => [
                'nullable',
                'string',
                'max:255',
            ],
            'codigo_postal' => [
                'nullable',
                'string',
                'max:10',
            ],
            'poblacion' => [
                'nullable',
                'string',
                'max:255',
            ],
            'activo' => [
                'boolean',
            ],
        ]);

        $proveedor = Proveedor::create($datos);

        return response()->json(
            $proveedor,
            201
        );
    }

    public function show(Proveedor $proveedor)
    {
        return response()->json($proveedor);
    }

    public function update(
        Request $request,
        Proveedor $proveedor
    ) {
        $datos = $request->validate([
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'nif' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('proveedores', 'nif')
                    ->where(
                        fn ($query) =>
                        $query->where(
                            'empresa_id',
                            $proveedor->empresa_id
                        )
                    )
                    ->ignore($proveedor->id),
            ],
            'telefono' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'contacto' => [
                'nullable',
                'string',
                'max:255',
            ],
            'direccion' => [
                'nullable',
                'string',
                'max:255',
            ],
            'codigo_postal' => [
                'nullable',
                'string',
                'max:10',
            ],
            'poblacion' => [
                'nullable',
                'string',
                'max:255',
            ],
            'activo' => [
                'boolean',
            ],
        ]);

        $proveedor->update($datos);

        return response()->json($proveedor);
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();

        return response()->json([
            'message' =>
                'Proveedor eliminado correctamente.',
        ]);
    }
}