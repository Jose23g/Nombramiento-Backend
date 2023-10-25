<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    use HasFactory;
    protected $table = 'actividades';
    protected $guarded = [];

    public function carga(): BelongsTo
    {
        return $this->belongsTo(Carga::class, 'carga_id', 'id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id');
    }

    public function pSeis(): BelongsTo
    {
        return $this->belongsTo(PSeis::class, 'p_seis_id', 'id');
    }
}
