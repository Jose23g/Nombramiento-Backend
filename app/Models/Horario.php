<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'horarios';
    // Relación muchos a uno con Dias
    public function dias()
    {
        return $this->belongsTo(Dias::class, 'id_dias');
    }
    // Relación uno a muchos con SolicitudGrupo
    public function solicitudGrupos()
    {
        return $this->hasMany(SolicitudGrupo::class, 'id_horario');
    }
}
