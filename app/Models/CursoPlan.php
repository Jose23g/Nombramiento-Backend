<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoPlan extends Model
{
    use HasFactory;
    protected $table = 'curso_plan';
    protected $guarded = [];

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id', 'id');
    }

    public function planEstudio()
    {
        return $this->belongsTo(PlanEstudios::class, 'plan_estudios_id', 'id');
    }
}
