<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioCarrera extends Model
{
    use HasFactory;
    protected $table = 'usuario_carreras';
    protected $guarded = [];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }
}
