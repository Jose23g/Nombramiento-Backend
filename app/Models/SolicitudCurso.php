<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudCurso extends Model
{
    use HasFactory;
    protected $table = 'solicitud_cursos';
    protected $guarded = [];
}
