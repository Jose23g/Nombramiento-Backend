<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrabajoDeclaracion extends Model
{
    use HasFactory;
    protected $table = 'trabajos_declaraciones';
    protected $guarded = [];

    public function declaracionJurada()
    {
        return $this->belongsTo(DeclaracionJurada::class, 'declaracion_jurada_id', 'id');
    }

    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class, 'trabajo_id', 'id');
    }
}
