<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'direcciones';

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

}
