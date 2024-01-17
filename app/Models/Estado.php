<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;
    protected $table = 'estados';
    protected $guarded = [];

    public function actividades()
    {
        return $this->hasMany(Actividades::class, 'estado_id', 'id');
    }

    public function trabajos()
    {
        return $this->hasMany(Trabajo::class, 'estado_id', 'id');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'estado_id', 'id');
    }

    public function solicitudCursos()
    {
        return $this->hasMany(SolicitudCurso::class, 'estado_id', 'id');
    }

    public function archivosEstado()
    {
        return $this->hasMany(Archivos::class, 'estado_id', 'id');
    }

    public function archivosEstadoGeneral()
    {
        return $this->hasMany(Archivos::class, 'estado_general_id', 'id');
    }
}
