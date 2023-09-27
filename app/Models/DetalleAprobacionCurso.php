<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleAprobacionCurso extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'detalle_aprobacion_cursos';
    // Relación muchos a uno con AprobacionSolicitudCurso
    public function aprobacionSolicitudCurso()
    {
        return $this->belongsTo(AprobacionSolicitudCurso::class, 'id_aprobacion');
    }
    // Relación muchos a uno con Curso
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }
}
