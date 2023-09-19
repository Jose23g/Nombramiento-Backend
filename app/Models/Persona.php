<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'personas';

    protected $fillable = [
        'cedula',
        'nombre',
        'id_provincia',
        'cuenta',
        'id_banco',
        'id_canton',
        'id_distrito',
        'id_barrio',
        'otrassenas'
    ];

    
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
    
    public function banco()
    {
        return $this->belongsTo(Banco::class, 'id_banco');
    }

    public function barrio()
    {
        return $this->belongsTo(Barrio::class, 'id_barrio');
    }

    public function distrito()
    {
        return $this->belongsTo(Distrito::class, 'id_distrito');
    }

    public function canton()
    {
        return $this->belongsTo(Canton::class, 'id_canton');
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'id_provincia');
    }

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_persona');
    }

}
