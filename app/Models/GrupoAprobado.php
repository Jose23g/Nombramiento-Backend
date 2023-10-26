<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoAprobado extends Model
{
    use HasFactory;
    protected $table = 'grupos_aprobados';
    protected $guarded = [];

    public function detalleAprobacionCurso()
    {
        return $this->belongsTo(DetalleAprobacionCurso::class, 'detalle_aprobado_id', 'id');
    }

    public function solicitudGrupo()
    {
        return $this->belongsTo(SolicitudGrupo::class, 'solicitud_grupo_id', 'id');
    }
}
