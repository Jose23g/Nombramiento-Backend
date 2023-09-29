<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class CoordinadorController extends Controller
{
    
    public function Solicitud_de_curso(Request $request){
      
        
        $validator = Validator::make($request->all(), 
            [
            'anio' => 'required',
            'semestre' => 'required',
            'id_coordinador' => 'required',
            'id_carrera' => 'required',
            'fecha' => 'required|date',
            'detalle_solicitud' => 'required|array|min:1',
            'detalle_solicitud.*.id_curso'=>'required',
            'detalle_solicitud.*.ciclo'=>'required',
            'detalle_solicitud.*.recinto'=>'required',
            'detalle_solicitud.*.carga'=>'required',
            'detalle_solicitud.*.solicitud_grupo' => 'required|array|min:1',
            'detalle_solicitud.*.solicitud_grupo.*.id_profesor' => 'required',
            'detalle_solicitud.*.solicitud_grupo.*.grupo' => 'required',
            'detalle_solicitud.*.solicitud_grupo.*.cupo' => 'required',
            'detalle_solicitud.*.solicitud_grupo.*.horario' => 'required|array|min:1',
            'detalle_solicitud.*.solicitud_grupo.*.horario.*.id_dia' => 'required',
            'detalle_solicitud.*.solicitud_grupo.*.horario.*.Entrada' => 'required',
            'detalle_solicitud.*.solicitud_grupo.*.horario.*.Salida' => 'required'
            ],
        ); 

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        
        DB::beginTransaction();
        try {

            
          foreach ($request->detalle_solicitud as $detalle){
           
                foreach ($detalle['solicitud_grupo'] as $solicitud_grupo){
                    

                 foreach ($solicitud_grupo['horario'] as $horario){
                

                 }
                }
           } 

     } catch(Exception $e){
         DB::rollback();
        return response()->json(['message' => $e->getMessage()], 422);
       
    }
    return response()->json(['message' =>'ha llegado el elemento'], 200);

       
    }

    public function Ver_Solicitud_curso(Request $request){

    }

    public function Editar_Solicitud_curso(Request $request){
        
    }

    public function Ver_Estado_Solicitud(Request $request){
        
    }
}
