<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeclaracionJurada extends Model
{
    use HasFactory;
    protected $table = 'declaraciones_juradas';
    protected $guarded = [];

    public function trabajos()
    {
        return $this->belongsToMany(Trabajo::class, 'trabajos_declaraciones', 'declaracion_jurada_id', 'trabajo_id');
    }
}
