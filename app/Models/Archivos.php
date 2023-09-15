<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archivos extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    protected $table = 'archivos';

    protected $fillable = [
        'nombre',
        'tipo',
        'file',
        'id_persona',
    ];


    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

}
