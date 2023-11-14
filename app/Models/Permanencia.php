<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permanencia extends Model
{
    use HasFactory;
    protected $table = 'permanencias';
    protected $guarded = [];

    public function fecha()
    {
        return $this->belongsTo(Fecha::class, 'fecha_id', 'id');
    }
}
