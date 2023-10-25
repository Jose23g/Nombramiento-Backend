<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleSolicitud extends Model
{
    use HasFactory;
    protected $table = 'detalle_solicitudes';
    protected $guarded = [];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }

    public function solicitudCurso(): BelongsTo
    {
        return $this->belongsTo(SolicitudCurso::class, 'solicitud_curso_id', 'id');
    }

    public function detalleAprobacionCursos(): HasMany
    {
        return $this->hasMany(DetalleAprobacionCurso::class, 'detalle_solicitud_id', 'id');
    }

    public function solicitudGrupos(): HasMany
    {
        return $this->hasMany(SolicitudGrupo::class, 'detalle_solicitud_id', 'id');
    }
}
