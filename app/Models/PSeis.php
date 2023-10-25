<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PSeis extends Model
{
    use HasFactory;
    protected $table = 'p_seis';
    protected $guarded = [];

    public function actividades(): HasMany
    {
        return $this->hasMany(Actividades::class, 'p_seis_id', 'id');
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'profesor_id', 'id');
    }

    public function solicitudGrupo(): BelongsTo
    {
        return $this->belongsTo(SolicitudGrupo::class, 'solicitud_grupo_id', 'id');
    }
}
