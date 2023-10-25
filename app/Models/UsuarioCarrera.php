<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioCarrera extends Model
{
    use HasFactory;
    protected $table = 'usuario_carreras';
    protected $guarded = [];
}
