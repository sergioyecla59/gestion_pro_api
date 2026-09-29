<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $fillable = [
        'nombre_comercial',
        'razon_social',
        'nif',
        'email',
        'telefono',
        'direccion',
        'codigo_postal',
        'localidad',
        'provincia',
        'pais',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
        ];
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }
    public function proveedores(): HasMany
    {
    return $this->hasMany(Proveedor::class);
    }
    
}
