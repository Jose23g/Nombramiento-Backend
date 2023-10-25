<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use HasFactory;
    protected $table = 'carreras';
    protected $guarded = [];

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'usuario_carreras', 'carrera_id', 'usuario_id');
    }

    public function usuarioCarreras(): HasMany
    {
        return $this->hasMany(UsuarioCarrera::class, 'carrera_id', 'id');
    }

    public function planEstudios(): HasMany
    {
        return $this->hasMany(PlanEstudios::class, 'carrera_id', 'id');
    }

    public function solicitudCursos(): HasMany
    {
        return $this->hasMany(SolicitudCurso::class, 'carrera_id', 'id');
    }
}
