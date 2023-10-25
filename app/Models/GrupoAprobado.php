<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoAprobado extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'grupo_aprobados';
    // Relación muchos a uno con DetalleAprobacionCurso
    public function detalleAprobacionCurso()
    {
        return $this->belongsTo(DetalleAprobacionCurso::class, 'id_detalle');
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
