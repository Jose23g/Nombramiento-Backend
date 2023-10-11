<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'carreras';

    protected $fillable = [
      'nombre',
    ];
    // Relación uno a muchos con PlanEstudio
    public function planesEstudio()
    {
        return $this->hasMany(PlanEstudios::class, 'id_carrera');
    }
    // Relación uno a muchos con SolicitudCurso
    public function solicitudesCarrera()
    {
        return $this->hasMany(SolicitudCurso::class, 'id_carrera');
    }
    public function coordinadores()
    {
        return $this->hasMany(UsuarioCarrera::class, 'id_carrera');
    }
    public function carreraUsuarios()
    {
        return $this->hasManyThrough(Usuario::class, UsuarioCarrera::class, 'id_carrera', 'id', 'id', 'id_coordinador');
    }
}
