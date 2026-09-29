<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Presupuesto extends Model
{
    protected $table = 'presupuestos';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'numero',
        'fecha',
        'valido_hasta',
        'estado',
        'base_imponible',
        'iva',
        'total',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'valido_hasta' => 'date',
            'base_imponible' => 'decimal:2',
            'iva' => 'decimal:2',
            'total' => 'decimal:2',
            'fecha_aceptacion' => 'datetime',
            'fecha_rechazo' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Presupuesto $presupuesto) {
            if (empty($presupuesto->token)) {
                $presupuesto->token = Str::random(64);
            }
        });
    }

    public function estaCaducado(): bool
    {
        return $this->valido_hasta !== null && $this->valido_hasta->endOfDay()->isPast();
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(LineaPresupuesto::class);
    }
}