<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jornada extends Model
{
    use HasFactory;
    protected $table = 'jornadas';
    protected $guarded = [];

    public function trabajos(): HasMany
    {
        return $this->hasMany(Trabajo::class, 'jornada_id', 'id');
    }
}
