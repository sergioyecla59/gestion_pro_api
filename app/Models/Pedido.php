<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'presupuesto_id',
        'numero',
        'fecha',
        'fecha_entrega',
        'fecha_envio',
        'fecha_completado',
        'fecha_cancelacion',
        'fecha_anulacion',
        'motivo_anulacion',
        'origen',
        'estado',
        'subtotal',
        'iva',
        'total',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'fecha_entrega' => 'date',
            'fecha_envio' => 'datetime',
            'fecha_completado' => 'datetime',
            'fecha_cancelacion' => 'datetime',
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

    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(Presupuesto::class);
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(LineaPedido::class);
    }

    public function albaran(): HasOne
    {
        return $this->hasOne(Albaran::class);
    }
}
