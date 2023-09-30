<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudGrupo extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'solicitud_grupos';

    protected $fillable = [
        'grupo',
        'cupo',
        'id_detalle',
        'id_profesor',
        'id_horario'
    ];
    // Relación muchos a uno con DetalleSolicitud
    public function detalleSolicitud()
    {
        return $this->belongsTo(DetalleSolicitud::class, 'id_detalle');
    }
    // Relación muchos a uno con Usuario (Profesor)
    public function profesor()
    {
        return $this->belongsTo(Usuario::class, 'id_profesor');
    }
    // Relación muchos a uno con Horario
    public function horario()
    {
        return $this->belongsTo(Horario::class, 'id_horario');
    }
}
