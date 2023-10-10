<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $fillable = [
        'nombre',
    ];
    
    use HasFactory;
    public function solicitudesCursos()
    {
        return $this->hasMany(SolicitudCurso::class, 'id_estado');
    }
}
