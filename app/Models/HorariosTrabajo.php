<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorariosTrabajo extends Model
{
    use HasFactory;
    protected $table = 'horarios_trabajos';
    protected $guarded = [];

    public function dia()
    {
        return $this->belongsTo(Dias::class, 'dia_id', 'id');
    }

    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class, 'trabajo_id', 'id');
    }
}
