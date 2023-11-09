<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PSeis extends Model
{
    use HasFactory;
    protected $table = 'p_seis';
    protected $guarded = [];

    public function actividades()
    {
        return $this->hasMany(Actividades::class, 'p_seis_id', 'id');
    }

    public function profesor()
    {
        return $this->belongsTo(Usuario::class, 'profesor_id', 'id');
    }

    public function solicitudGrupo()
    {
        return $this->belongsTo(SolicitudGrupo::class, 'solicitud_grupo_id', 'id');
    }

    public function trabajos()
    {
        return $this->belongsToMany(Trabajo::class, 'trabajos_p_seis', 'p_seis_id', 'trabajo_id');
    }
}
