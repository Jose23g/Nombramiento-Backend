<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Titulo extends Model
{
    use HasFactory;
    protected $table = 'titulos';
    protected $guarded = [];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }
}
