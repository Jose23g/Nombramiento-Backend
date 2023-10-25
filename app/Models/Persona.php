<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'personas';
    protected $guarded = [];
    public $timestamps = false;

    public function archivos(): HasMany
    {
        return $this->hasMany(Archivos::class, 'persona_id', 'id');
    }

    public function telefono(): HasOne
    {
        return $this->hasOne(Telefono::class, 'persona_id', 'id');
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id', 'id');
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class, 'provincia_id', 'id');
    }

    public function canton(): BelongsTo
    {
        return $this->belongsTo(Canton::class, 'canton_id', 'id');
    }

    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class, 'distrito_id', 'id');
    }

    public function usuario(): HasOne
    {
        return $this->hasOne(Usuario::class, 'persona_id', 'id');
    }
}
