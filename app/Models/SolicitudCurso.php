<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudCurso extends Model
{
    use HasFactory;
    protected $table = 'solicitud_cursos';
    protected $guarded = [];

    public function aprobacionSolicitudCursos()
    {
        return $this->hasMany(AprobacionSolicitudCurso::class, 'solicitud_curso_id', 'id');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id', 'id');
    }

    public function detalleSolicitudes()
    {
        return $this->hasMany(DetalleSolicitud::class, 'solicitud_curso_id', 'id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id');
    }

    public function fecha()
    {
        return $this->belongsTo(Fecha::class, 'fecha_id', 'id');
    }

    public function coordinador()
    {
        return $this->belongsTo(Usuario::class, 'coordinador_id', 'id');
    }
}
