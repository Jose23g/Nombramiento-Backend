<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;
    protected $guarded = [];
    // Relación muchos a muchos con planes de estudio
    protected $table = 'cursos';
    // Relación uno a muchos con CursoPlan
    public function planesCurso()
    {
        return $this->hasMany(CursoPlan::class, 'id_curso');
    }
    // Relación uno a muchos con DetalleSolicitud
    public function detallesCurso()
    {
        return $this->hasMany(DetalleSolicitud::class, 'id_curso');
    }
    // Relación uno a muchos con DetalleAprobacionCurso
    public function detallesAprobacionCurso()
    {
        return $this->hasMany(DetalleAprobacionCurso::class, 'id_curso');
    }
}
