<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function canton()
    {
        return $this->belongsTo(Canton::class, 'id_canton');
    }

    public function barrios()
    {
        return $this->hasMany(Barrio::class, 'id_distrito');
    }

}
