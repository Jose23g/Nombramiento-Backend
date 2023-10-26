<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FechaSolicitud extends Model
{
    use HasFactory;
    protected $table = 'fecha_solicitudes';
    protected $guarded = [];

    public function solicitudCursos()
    {
        return $this->hasMany(SolicitudCurso::class, 'fecha_solicitud_id', 'id');
    }
}
