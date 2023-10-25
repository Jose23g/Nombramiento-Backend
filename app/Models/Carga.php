<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carga extends Model
{
    use HasFactory;
    protected $table = 'cargas';
    protected $guarded = [];

    public function actividades(): HasMany
    {
        return $this->hasMany(Actividades::class, 'carga_id', 'id');
    }

    public function solicitudGrupos(): HasMany
    {
        return $this->hasMany(SolicitudGrupo::class, 'carga_id', 'id');
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'carga_id', 'id');
    }
}
