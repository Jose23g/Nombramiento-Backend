<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fecha extends Model
{
    use HasFactory;
    protected $table = 'fechas';
    protected $guarded = [];

    public function solicitudCursos()
    {
        return $this->hasMany(SolicitudCurso::class, 'fecha_id', 'id');
    }

    public function trabajos()
    {
        return $this->hasMany(Trabajo::class, 'fecha_id', 'id');
    }

    public function permanencias()
    {
        return $this->hasMany(Permanencia::class, 'fecha_id', 'id');
    }
}
