<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dias extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'dias';
    
    protected $fillable = [
        'entrada',
        'salida',
        'id_dia',
        'id_horario'
    ];
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'id_dias');
    }
    // Relación muchos a uno con Dia
    public function dia()
    {
        return $this->belongsTo(Dia::class, 'id_dia');
    }
}
