<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    use HasFactory;
    protected $table = 'actividades';
    protected $guarded = [];

    public function carga(): BelongsTo
    {
        return $this->belongsTo(Carga::class, 'id', 'carga_id');
    }
}
