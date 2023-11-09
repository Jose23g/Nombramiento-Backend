<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrabajoPSeis extends Model
{
    use HasFactory;
    protected $table = 'trabajos_p_seis';
    protected $guarded = [];

    public function pSeis()
    {
        return $this->belongsTo(PSeis::class, 'p_seis_id', 'id');
    }

    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class, 'trabajo_id', 'id');
    }
}
