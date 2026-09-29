<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Mostrar todos los clientes.
     */
    public function index()
    {
        $clientes = Cliente::orderBy('nombre')->get();

        return response()->json($clientes);
    }

    /**
     * Crear un nuevo cliente.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'empresa_id' => ['required', 'exists:empresas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'nif' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
            'localidad' => ['nullable', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
            'pais' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
            'notas' => ['nullable', 'string'],
        ]);

        $cliente = Cliente::create($datos);

        return response()->json($cliente, 201);
    }

    /**
     * Mostrar un cliente concreto.
     */
    public function show(Cliente $cliente)
    {
        return response()->json($cliente);
    }

    /**
     * Actualizar un cliente.
     */
    public function update(Request $request, Cliente $cliente)
    {
        $datos = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'nif' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
            'localidad' => ['nullable', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
            'pais' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
            'notas' => ['nullable', 'string'],
        ]);

        $cliente->update($datos);

        return response()->json($cliente);
    }

    /**
     * Eliminar un cliente.
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return response()->json([
            'message' => 'Cliente eliminado correctamente.',
        ]);
    }
}