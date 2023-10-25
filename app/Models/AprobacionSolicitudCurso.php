<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AprobacionSolicitudCurso extends Model
{
    use HasFactory;
    protected $table = 'aprobacion_solicitud_cursos';
    protected $guarded = [];
}
