<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Albaran extends Model
{
    protected $table = 'albaranes';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'pedido_id',
        'numero',
        'fecha',
        'fecha_entrega',
        'estado',
        'subtotal',
        'iva',
        'total',
        'observaciones',
        'fecha_anulacion',
        'motivo_anulacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'fecha_entrega' => 'datetime',
            'fecha_anulacion' => 'datetime',
            'subtotal' => 'decimal:2',
            'iva' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(LineaAlbaran::class);
    }
}
