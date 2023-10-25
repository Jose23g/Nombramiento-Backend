<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PSeis extends Model
{
    use HasFactory;
    protected $table = 'p_seis';
    protected $guarded = [];
}
