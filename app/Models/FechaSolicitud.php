<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FechaSolicitud extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'fechas_solicitudes';

    protected $fillable = [
        'nombre',
        'anio',
        'semestre',
        'fecha_inicio',
        'fecha_fin',
    ];
}