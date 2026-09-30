<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineaPedido extends Model
{
    protected $table = 'lineas_pedido';

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'porcentaje_iva',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'porcentaje_iva' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
