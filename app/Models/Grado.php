<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'grados';
    // Relación uno a muchos con PlanEstudio
    public function planesEstudio()
    {
        return $this->hasMany(PlanEstudio::class, 'id_grado');
    }
}
