<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Canton extends Model
{
    use HasFactory;
    protected $table = 'cantones';
    protected $guarded = [];

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class, 'provincia_id', 'id');
    }

    public function distritos(): HasMany
    {
        return $this->hasMany(Distrito::class, 'canton_id', 'id');
    }

    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class, 'canton_id', 'id');
    }
}
