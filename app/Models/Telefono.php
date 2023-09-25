<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Telefono extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'telefonos';
    
    protected $fillable = [
        'id_persona',
        'personal',
        'trabajo',
         'otro'
    ];
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

}
