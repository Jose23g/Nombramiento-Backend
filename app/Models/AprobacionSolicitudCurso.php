<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AprobacionSolicitudCurso extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'aprobacion_solicitud_cursos';
    // Relación muchos a uno con SolicitudCurso
    public function solicitudCurso()
    {
        return $this->belongsTo(SolicitudCurso::class, 'id_solicitud');
    }
    // Relación muchos a uno con Usuario (Encargado)
    public function encargado()
    {
        return $this->belongsTo(Usuario::class, 'id_encargado');
    }
    // Relación uno a muchos con DetalleAprobacionCurso
    public function detallesAprobacionCurso()
    {
        return $this->hasMany(DetalleAprobacionCurso::class, 'id_aprobacion');
    }
}
