<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trabajo extends Model
{
    use HasFactory;
    protected $table = 'trabajos';
    protected $guarded = [];

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id');
    }

    public function jornada()
    {
        return $this->belongsTo(Jornada::class, 'jornada_id', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }

    public function horarioTrabajos()
    {
        return $this->hasMany(HorariosTrabajo::class, 'trabajo_id', 'id');
    }

    public function pSeis()
    {
        return $this->belongsToMany(PSeis::class, 'trabajos_p_seis', 'trabajo_id', 'p_seis_id');
    }

    public function declaracionesJuradas()
    {
        return $this->belongsToMany(DeclaracionJurada::class, 'trabajos_declaraciones', 'trabajo_id', 'declaracion_jurada_id');
    }

    public function fecha()
    {
        return $this->belongsTo(Fecha::class, 'fecha_id', 'id');
    }
}
