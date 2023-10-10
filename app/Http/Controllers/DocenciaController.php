<?php

namespace App\Http\Controllers;

use App\Models\Estado;
use App\Models\FechaSolicitud;
use App\Models\SolicitudCurso;
use App\Models\Persona;
use App\Models\Usuario;
use App\Models\Carrera;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class DocenciaController extends Controller
{
    public function fechaRecepcion(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'anio' => 'required',
                    'semestre' => 'required',
                    'fecha_inicio' => 'required',
                    'fecha_fin' => 'required',
                    'nombre' => 'required',
                ],
                [
                    'anio.required' => 'Es anio no puede estar vacío',
                    'semestre.required' => 'El semestre no puede estar vacío',
                    'fecha_inicio.required' => 'Es necesario establecer una fecha de inicio',
                    'fecha_fin.required' => 'Es necesario establecer una fecha de final',
                    'nombre.required' => 'Es necesario un nombre',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
            FechaSolicitud::create([
                'nombre' => $request->input('nombre'),
                'anio' => $request->input('anio'),
                'semestre' => $request->input('semestre'),
                'fecha_inicio' => $request->input('fecha_inicio'),
                'fecha_fin' => $request->input('fecha_fin'),
            ]);
            return response()->json(['message' => 'Plazo para la recepción de solicutudes de cursos establecida']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function comprobarFechaRecepcion(Request $request)
    {
        $fechaActual = Carbon::now();
        $fechaSolicitud = FechaSolicitud::where('anio', $request->input('anio'))->where('semestre', $request->input('semestre'))->first();

        if (!$fechaSolicitud || !$fechaActual->between($fechaSolicitud->fecha_inicio, $fechaSolicitud->fecha_fin)) {
            return response()->json(['error' => 'El periodo para realizar la solicitud de curso ha finalizado o no está disponible'], 400);
        }

        return response()->json(['messaje' => 'se puede hacer la solicitud'], 200);
    }

    public function Listar_fechas_solicitudes(Request $request)
    {

        try {
            $todasfechas = FechaSolicitud::all();

            if (!$todasfechas) {
                return response()->json(['message' => 'No hay fechas registradas'], 500);
            }

            return response()->json(['Fechas de Solictud' => $todasfechas]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function Ver_Solicitud_curso_fecha(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_fecha' => 'required'
        ]);


        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $verificarfecha = FechaSolicitud::Where('id', $request->id_fecha)->first();

        if (!$verificarfecha) {
            return response()->json(['message' => 'Error al seleccionar la fecha'], 422);
        }

        try {

            $solicitudcursos = SolicitudCurso::whereBetween('fecha', [$verificarfecha->fecha_inicio, $verificarfecha->fecha_fin])->get();

            if (!$solicitudcursos) {
                return response()->json(['message' => 'no hay solicitudes en el lapso consultado'], 422);
            }
            return response()->json([
                'fecha_inicio' => $verificarfecha->fecha_inicio,
                'fecha_fin' => $verificarfecha->fecha_fin,
                'solicitudes' => $solicitudcursos
            ], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        
    }
       public function Listar_todas_solicitudes(Request $request ){
        $solicitudcompleta = [];
       
        try{
            $solicitudcursos = SolicitudCurso::all();

            if($solicitudcursos== null){
                return response()->json(['Message' => 'No hay solicitudes de cursos'], 200);
            }

            foreach($solicitudcursos as $solicitud){
              $nombrecarrera = Carrera::where('id', $solicitud->id_carrera )->select('nombre')->first();
              $usuario = Usuario::where('id', $solicitud->id_coordinador)->first();
              $nombrepersona = Persona::where('id', $usuario->id_persona)->select('nombre')->first();
              $estado = Estado::where('id', $solicitud->id_estado)->select('nombre')->first();
            
              $solicitudarreglo = [
                    'id'=> $solicitud->id,
                    'fecha' => $solicitud->fecha,
                    'semestre'=> $solicitud->semestre,
                    'carrera' =>$nombrecarrera->nombre,
                    'coordinador'=> $nombrepersona->nombre,
                    'estado' => $estado->nombre];

                $detalles[] = $solicitudarreglo;
                
            }

            return response()->json(['Solicitudes de curso' => $detalles], 200);

        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
            

            
        }
    


        public function cambiarEstadoSolicitud(Request $request)
        {
            try {
                $solicitudCurso = SolicitudCurso::Where('id', $request->input('id_solicitud'))->first();
                $estado = Estado::Where('nombre', $request->input('estado'))->first();
                //dd($estado);
    
                if ($request->input('estado') == 'Aceptado') {
                    $solicitudCurso->id_estado = $estado->id;
                    $solicitudCurso->save();
                    return response()->json(['success' => true, 'message' => 'Se ha aceptado la solicitud'], 200);
                }
                $solicitudCurso->id_estado = $estado->id;
                $solicitudCurso->observacion = $request->input('observacion');
                $solicitudCurso->save();
    
                return response()->json(['success' => true, 'message' => 'Se ha rechazado la solicitud'], 200);
    
            }catch (Exception $e){
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
    }
    
