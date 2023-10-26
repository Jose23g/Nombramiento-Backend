<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dias extends Model
{
    use HasFactory;
    protected $table = 'dias';
    protected $guarded = [];

    public function horarioGrupos()
    {
        return $this->hasMany(HorariosGrupo::class, 'dia_id', 'id');
    }

    public function horarioTrabajo()
    {
        return $this->hasMany(HorariosTrabajo::class, 'dia_id', 'id');
    }
}
