<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorariosTrabajo extends Model
{
    use HasFactory;
    protected $table = 'horarios_trabajo';
    protected $guarded = [];

    public function dia(): BelongsTo
    {
        return $this->belongsTo(Dias::class, 'dia_id', 'id');
    }

    public function trabajo(): BelongsTo
    {
        return $this->belongsTo(Trabajo::class, 'trabajo_id', 'id');
    }
}
