<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AprobacionSolicitudCurso extends Model
{
    use HasFactory;
    protected $table = 'aprobacion_solicitud_cursos';
    protected $guarded = [];

    public function solicitudCurso(): BelongsTo
    {
        return $this->belongsTo(SolicitudCurso::class, 'solicitud_curso_id', 'id');
    }

    public function encargado(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'encargado_id', 'id');
    }

    public function detalleAprobacionCursos(): HasMany
    {
        return $this->hasMany(DetalleAprobacionCurso::class, 'curso_aprobado_id', 'id');
    }
}
