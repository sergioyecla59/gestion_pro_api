<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Serie extends Model
{
    protected $table = 'series';

    protected $fillable = [
        'empresa_id',
        'tipo',
        'prefijo',
        'anio',
        'ultimo_numero',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'ultimo_numero' => 'integer',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
