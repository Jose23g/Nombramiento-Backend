<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudCurso extends Model
{
    use HasFactory;
    protected $table = 'solicitud_cursos';
    protected $guarded = [];

    public function aprobacionSolicitudCursos(): HasMany
    {
        return $this->hasMany(AprobacionSolicitudCurso::class, 'solicitud_curso_id', 'id');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_id', 'id');
    }

    public function detalleSolicitudes(): HasMany
    {
        return $this->hasMany(DetalleSolicitud::class, 'solicitud_curso_id', 'id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id');
    }

    public function fechaSolicitud(): BelongsTo
    {
        return $this->belongsTo(FechaSolicitud::class, 'fecha_solicitud_id', 'id');
    }

    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'coordinador_id', 'id');
    }
}
