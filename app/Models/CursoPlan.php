<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoPlan extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'curso_planes';
    // Relación muchos a uno con Curso
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }
    // Relación muchos a uno con PlanEstudio
    public function planEstudio()
    {
        return $this->belongsTo(PlanEstudio::class, 'id_plan');
    }
}
