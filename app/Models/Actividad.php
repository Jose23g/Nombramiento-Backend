<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    use HasFactory;
    protected $table = 'actividades';
    protected $guarded = [];

    public function carga()
    {
        return $this->belongsTo(Carga::class, 'carga_id', 'id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id');
    }

    public function pSeis()
    {
        return $this->belongsTo(PSeis::class, 'p_seis_id', 'id');
    }
}
