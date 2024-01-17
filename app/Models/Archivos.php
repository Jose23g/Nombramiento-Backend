<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archivos extends Model
{
    use HasFactory;
    protected $table = 'archivos';
    protected $guarded = [];

    public function propietario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_propietario_id', 'id');
    }

    public function coordinador()
    {
        return $this->belongsTo(Usuario::class, 'usuario_coordinador_id', 'id');
    }

    public function direccion()
    {
        return $this->belongsTo(Usuario::class, 'usuario_direccion_id', 'id');
    }

    public function docencia()
    {
        return $this->belongsTo(Usuario::class, 'usuario_docencia_id', 'id');
    }
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id');
    }
    public function estadoGeneral()
    {
        return $this->belongsTo(Estado::class, 'estado_general_id', 'id');
    }
}
