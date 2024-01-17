<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Estado;
use App\Models\Fecha;
use App\Models\Persona;
use App\Models\SolicitudCurso;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DirectorContoller extends Controller
{
    public function obtener_solicitudes_pendientes()
    {
        try {

            $consultaestados = app()->make(EstadosController::class);
            $categoria = $consultaestados->Estado_por_nombre('pendiente');
            $solicitudes = SolicitudCurso::where('estado_id', $categoria)->get();

            if ($solicitudes == null) {
                return response()->json(['Message' => 'No hay solicitudes de cursos'], 200);
            }

            foreach ($solicitudes as $solicitud) {
                $nombrecarrera = Carrera::where('id', $solicitud->carrera_id)->select('nombre')->first();
                $usuario = Usuario::where('id', $solicitud->coordinador_id)->first();
                $nombrepersona = Persona::where('id', $usuario->persona_id)->select('nombre')->first();
                $estado = Estado::where('id', $solicitud->estado_id)->select('nombre')->first();
                $semestre = Fecha::where('id', $solicitud->fecha_id)->first();
                $fecha = Carbon::parse($solicitud->created_at)->format('Y-m-d');

                $solicitudarreglo = (object) [
                    'id' => $solicitud->id,
                    'fecha' => $fecha,
                    'semestre' => $semestre->ciclo,
                    'carrera' => $nombrecarrera->nombre,
                    'coordinador' => $nombrepersona->nombre,
                    'estado' => $estado->nombre,
                ];

                $detalles[] = $solicitudarreglo;
            }
            return response()->json(['Solicitudes_de_curso' => $detalles], 200);
       
        } catch (\Exception $e) {
            
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }
}
