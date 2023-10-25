<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dia extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'dia';
    // Relación uno a muchos con DetalleDia
    public function detallesDia()
    {
        return $this->hasMany(DetalleDia::class, 'id_dia');
    }
}
