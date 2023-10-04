<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banco;

class BancoController extends Controller
{
    public function obtengaLaLista(){
        return Banco::all()->toJson();
    }
}
