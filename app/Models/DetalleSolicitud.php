<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleSolicitud extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'detalle_solicitudes';

    protected $fillable = [
        'ciclo',
        'grupos',
        'recinto',
        'carga',
         'id_solicitud',
         'id_curso',
    ];

    // Relación muchos a uno con SolicitudCurso
    public function solicitudCurso()
    {
        return $this->belongsTo(SolicitudCurso::class, 'id_solicitud');
    }

    public function solicitudGrupo()
    {
        return $this->hasMany(SolicitudGrupo::class, 'id_detalle');
    }

    // Relación muchos a uno con Curso
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }
}
