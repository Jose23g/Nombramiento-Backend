<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoPlan extends Model
{
    use HasFactory;
    protected $table = 'curso_plan';
    protected $guarded = [];
}
