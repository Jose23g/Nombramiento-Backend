<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleAprobacionCurso extends Model
{
    use HasFactory;
    protected $table = 'detalle_aprobacion_cursos';
    protected $guarded = [];

    public function aprobacionSolicitudCurso()
    {
        return $this->belongsTo(AprobacionSolicitudCurso::class, 'curso_aprobado_id', 'id');
    }

    public function detalleSolicitud()
    {
        return $this->belongsTo(DetalleSolicitud::class, 'detalle_solicitud_id', 'id');
    }

    public function gruposAprobados()
    {
        return $this->hasMany(GrupoAprobado::class, 'detalle_aprobado_id', 'id');
    }
}
