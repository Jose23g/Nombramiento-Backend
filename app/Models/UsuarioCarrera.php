<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioCarrera extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'usuario_carreras';

    public function coordinador()
    {
        return $this->belongsTo(Usuario::class, 'id_coordinador');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }

}
