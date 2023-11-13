<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banco extends Model
{
    use HasFactory;
    protected $table = 'bancos';

    protected $guarded = [];

    public function persona()
    {
        return $this->hasMany(Persona::class, 'banco_id', 'id');
    }
}
