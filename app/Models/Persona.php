<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'personas';
    protected $guarded = [];
    public $timestamps = false;

    public function archivo()
    {
        return $this->hasOne(Archivos::class, 'persona_id', 'id');
    }

    public function telefono()
    {
        return $this->hasOne(Telefono::class, 'persona_id', 'id');
    }

    public function banco()
    {
        return $this->belongsTo(Banco::class, 'banco_id', 'id');
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'provincia_id', 'id');
    }

    public function canton()
    {
        return $this->belongsTo(Canton::class, 'canton_id', 'id');
    }

    public function distrito()
    {
        return $this->belongsTo(Distrito::class, 'distrito_id', 'id');
    }

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'persona_id', 'id');
    }
}
