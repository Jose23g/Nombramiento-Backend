<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    use HasFactory;
    protected $table = 'provincias';
    protected $guarded = [];

    public function cantones()
    {
        return $this->hasMany(Canton::class, 'provincia_id', 'id');
    }

    public function personas()
    {
        return $this->hasMany(Persona::class, 'provincia_id', 'id');
    }
}
