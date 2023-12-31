<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorariosGrupo extends Model
{
    use HasFactory;
    protected $table = 'horarios_grupos';
    protected $guarded = [];

    public function dia()
    {
        return $this->belongsTo(Dias::class, 'dia_id', 'id');
    }

    public function solicitudGrupo()
    {
        return $this->belongsTo(SolicitudGrupo::class, 'solicitud_grupo_id', 'id');
    }
}
