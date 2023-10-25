<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoAprobado extends Model
{
    use HasFactory;
    protected $table = 'grupos_aprobados';
    protected $guarded = [];
}
