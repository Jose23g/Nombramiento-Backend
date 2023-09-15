<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barrio extends Model
{
    use HasFactory;
    protected $guarded = [];

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

    public function personas()
    {
        return $this->hasMany(Persona::class, 'id_barrio');
    }

}
