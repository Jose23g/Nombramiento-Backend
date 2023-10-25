<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudGrupo extends Model
{
    use HasFactory;
    protected $table = 'solicitud_grupos';
    protected $guarded = [];
}
