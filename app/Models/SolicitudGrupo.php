<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudGrupo extends Model
{
    use HasFactory;
    protected $table = 'solicitud_grupos';
    protected $guarded = [];

    public function carga(): BelongsTo
    {
        return $this->belongsTo(Carga::class, 'carga_id', 'id');
    }

    public function detalleSolicitud(): BelongsTo
    {
        return $this->belongsTo(DetalleSolicitud::class, 'detalle_solicitud_id', 'id');
    }

    public function gruposAprobados(): HasMany
    {
        return $this->hasMany(GrupoAprobado::class, 'solicitud_grupo_id', 'id');
    }

    public function horarioGrupos(): HasMany
    {
        return $this->hasMany(HorariosGrupo::class, 'solicitud_grupo_id', 'id');
    }

    public function pSeis(): HasMany
    {
        return $this->hasMany(PSeis::class, 'solicitud_grupo_id', 'id');
    }
}
