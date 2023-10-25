<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleAprobacionCurso extends Model
{
    use HasFactory;
    protected $table = 'detalle_aprobacion_cursos';
    protected $guarded = [];
}
