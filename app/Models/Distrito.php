<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    use HasFactory;
    protected $table = 'distritos';
    protected $guarded = [];

    public function canton()
    {
        return $this->belongsTo(Canton::class, 'canton_id', 'id');
    }

    public function personas()
    {
        return $this->hasMany(Persona::class, 'distrito_id', 'id');
    }
}
