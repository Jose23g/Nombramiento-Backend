<?php

namespace App\Http\Controllers;

use App\Models\FechaSolicitud;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\SolicitudCurso;
use App\Models\SolicitudGrupo;
use App\Models\Horario;
use App\Models\Dias;
use App\Models\DetalleSolicitud;

use Illuminate\Http\Request;

class CoordinadorController extends Controller
{

    public function Solicitud_de_curso(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'anio' => 'required',
                'semestre' => 'required',
                'id_carrera' => 'required',
                'fecha' => 'required|date',
                'detalle_solicitud' => 'required|array|min:1',
                'detalle_solicitud.*.id_curso' => 'required',
                'detalle_solicitud.*.ciclo' => 'required',
                'detalle_solicitud.*.recinto' => 'required',
                'detalle_solicitud.*.carga' => 'required',
                'detalle_solicitud.*.solicitud_grupo' => 'required|array|min:1',
                'detalle_solicitud.*.solicitud_grupo.*.id_profesor' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.grupo' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.cupo' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.horario' => 'required|array|min:1',
                'detalle_solicitud.*.solicitud_grupo.*.horario.*.id_dia' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.horario.*.entrada' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.horario.*.salida' => 'required'
            ],
        );

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $fechaActual = Carbon::now();
            $fechaSolicitud = FechaSolicitud::where('anio', $request->input('anio'))->where('semestre', $request->input('semestre'))->first();
            $existesolicitud = SolicitudCurso::where('anio', $request->input('anio'))->where('semestre', $request->input('semestre'))->where('id_carrera', $request->input('id_carrera'))->first();
            if ($existesolicitud) {
                return response()->json([
                    'error' => 'Ya hay una solicitud que coincide con',
                    'anio' => $request->anio,
                    'semestre' => $request->semestre,
                    'id_carrera' => $request->id_carrera
                ], 400);
            }
            if (!$fechaSolicitud || !$fechaActual->between($fechaSolicitud->fecha_inicio, $fechaSolicitud->fecha_fin)) {
                return response()->json(['error' => 'El periodo para realizar la solicitud de curso ha finalizado o no esta disponible'], 400);
            }

            $usuario = $request->user();

            $nuevasolicitud = SolicitudCurso::create([
                'anio' => $request->anio,
                'semestre' => $request->semestre,
                'id_coordinador' => $usuario->id,
                'id_carrera' => $request->id_carrera,
                'id_estado' => 1,
                'fecha' => Carbon::now()->format('Y-m-d')
            ]);

            foreach ($request->detalle_solicitud as $detalle) {

                try {
                    $nuevodetalle = DetalleSolicitud::create([
                        'ciclo' => $detalle['ciclo'],
                        'grupos' => 2,
                        'recinto' => $detalle['recinto'],
                        'carga' => $detalle['carga'],
                        'id_solicitud' => $nuevasolicitud->id,
                        'id_curso' => $detalle['id_curso'],
                    ]);

                } catch (Exception $e) {
                    DB::rollback();
                    return response()->json(['message' => $e->getMessage()], 422);
                }
                foreach ($detalle['solicitud_grupo'] as $solicitud_grupo) {
                    //primero añadimos el horario general con su tipo 
                    try {
                        $nuevohorario = Horario::create([
                            'tipo' => "SolicitudGrupo"
                        ]);

                        ;
                    } catch (Exeption $e) {
                        DB::rollback();
                        return response()->json(['message' => $e->getMessage()], 422);
                    }


                    foreach ($solicitud_grupo['horario'] as $dias) {

                        //verificamos los dias que viene dentro del arreglo de horarios para cada curso
                        try {

                            $nuevodia = Dias::create([
                                'id_dia' => $dias['id_dia'],
                                'entrada' => $dias['entrada'],
                                'salida' => $dias['salida'],
                                'id_horario' => $nuevohorario->id
                            ]);

                        } catch (Exception $e) {
                            DB::rollback();
                            return response()->json(['message' => $e->getMessage()], 422);
                        }
                    }
                    // ahora una vez registrados los datos del horario Horario y los dia del grupo hacemos la solicitud por grupo
                    try {

                        $nuevogrupo = SolicitudGrupo::create([
                            'grupo' => $solicitud_grupo['grupo'],
                            'cupo' => $solicitud_grupo['cupo'],
                            'id_detalle' => $nuevodetalle->id,
                            'id_profesor' => $solicitud_grupo['id_profesor'],
                            'id_horario' => $nuevohorario->id
                        ]);


                    } catch (Exeption $e) {
                        DB::rollback();
                        return response()->json(['message' => $e->getMessage()], 422);
                    }
                }
            }

        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 422);

        }
        DB::commit();
        return response()->json(['message' => 'Se ha creado la solicitud de curso con éxito', 'Solicitud' => $nuevasolicitud], 200);

    }



    public function Ver_Estado_Solicitud(Request $request)
    {

    }
}