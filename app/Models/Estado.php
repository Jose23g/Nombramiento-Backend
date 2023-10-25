<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;
    protected $table = 'estados';
    protected $guarded = [];

    public function actividades(): HasMany
    {
        return $this->hasMany(Actividades::class, 'estado_id', 'id');
    }

    public function trabajos(): HasMany
    {
        return $this->hasMany(Trabajo::class, 'estado_id', 'id');
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'estado_id', 'id');
    }

    public function solicitudCursos(): HasMany
    {
        return $this->hasMany(SolicitudCurso::class, 'estado_id', 'id');
    }
}
