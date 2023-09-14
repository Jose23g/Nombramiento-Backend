<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function correos()
    {
        return $this->hasMany(Correo::class, 'id_persona');
    }

    public function telefonos()
    {
        return $this->hasMany(Telefono::class, 'id_persona');
    }

    public function archivos()
    {
        return $this->hasMany(Archivo::class, 'id_persona');
    }

    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'id_persona');
    }

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_persona');
    }

}
