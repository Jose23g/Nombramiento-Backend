<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanEstudios extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'plan_estudios';
    // Relación muchos a uno con Carrera
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }
    // Relación muchos a uno con Grado
    public function grado()
    {
        return $this->belongsTo(Grado::class, 'id_grado');
    }
    // Relación uno a muchos con CursoPlan
    public function planesCurso()
    {
        return $this->hasMany(CursoPlan::class, 'id_plan');
    }
    public function planCursos()
    {
        return $this->hasManyThrough(Curso::class, CursoPlan::class, 'id_plan', 'id', 'id', 'id_curso');
    }
}
