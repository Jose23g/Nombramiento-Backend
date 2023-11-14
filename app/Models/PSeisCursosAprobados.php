<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PSeisCursosAprobados extends Model
{
    use HasFactory;
    protected $table = 'p_seis_cursos_aprobados';
    protected $guarded = [];

    public function pSeis()
    {
        return $this->belongsTo(PSeis::class, 'p_seis_id', 'id');
    }

    public function cursoAprobado()
    {
        return $this->belongsTo(AprobacionSolicitudCurso::class, 'curso_aprobado_id', 'id');
    }
}
