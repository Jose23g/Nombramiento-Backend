<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;
    protected $table = 'cursos';
    protected $guarded = [];

    public function carga(): BelongsTo
    {
        return $this->belongsTo(Carga::class, 'carga_id', 'id');
    }

    public function planEstudios(): BelongsToMany
    {
        return $this->belongsToMany(PlanEstudios::class, 'curso_plan', 'curso_id', 'plan_estudios_id');
    }

    public function cursoPlanes(): HasMany
    {
        return $this->hasMany(CursoPlan::class, 'curso_id', 'id');
    }

    public function detalleSolicitudes(): HasMany
    {
        return $this->hasMany(DetalleSolicitud::class, 'curso_id', 'id');
    }
}
