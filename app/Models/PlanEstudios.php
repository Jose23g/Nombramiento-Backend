<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanEstudios extends Model
{
    use HasFactory;
    protected $table = 'plan_estudios';
    protected $guarded = [];

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_id', 'id');
    }

    public function cursos(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_plan', 'plan_estudios_id', 'curso_id');
    }

    public function cursoPlanes(): HasMany
    {
        return $this->hasMany(CursoPlan::class, 'plan_estudios_id', 'id');
    }

    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_id', 'id');
    }
}
