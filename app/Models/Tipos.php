<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipos extends Model
{
    use HasFactory;
    protected $table = 'tipos';
    protected $guarded = [];

    public function fechas(){
        return $this->belongsTo(Fecha::class, 'tipo_id', 'id');
    }
}
