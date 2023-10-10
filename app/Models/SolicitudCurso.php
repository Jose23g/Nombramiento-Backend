<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudCurso extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'solicitud_cursos';

    protected $fillable = [
        'anio',
        'semestre',
        'id_coordinador',
        'id_carrera',
        'id_estado',
        'fecha',
        'observacion'
    ];


    // Relación muchos a uno con Usuario (Coordinador)
    public function coordinador()
    {
        return $this->belongsTo(Usuario::class, 'id_coordinador');
    }
    // Relación muchos a uno con Carrera
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }
    // Relación uno a muchos con DetalleSolicitud
    public function detallesSolicitud()
    {
        return $this->hasMany(DetalleSolicitud::class, 'id_solicitud');
    }
    // Relación uno a uno con AprobacionSolicitudCurso
    public function aprobacionSolicitudCurso()
    {
        return $this->hasOne(AprobacionSolicitudCurso::class, 'id_solicitud');
    }
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_estado');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_coordinador');
    }
}
