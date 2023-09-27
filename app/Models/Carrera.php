<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'carreras';
    // Relación uno a muchos con PlanEstudio
    public function planesEstudio()
    {
        return $this->hasMany(PlanEstudio::class, 'id_carrera');
    }
    // Relación uno a muchos con SolicitudCurso
    public function solicitudesCarrera()
    {
        return $this->hasMany(SolicitudCurso::class, 'id_carrera');
    }
}
