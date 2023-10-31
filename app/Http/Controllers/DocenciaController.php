<?php

namespace App\Http\Controllers;

use App\Models\AprobacionSolicitudCurso;
use App\Models\DetalleAprobacionCurso;
use App\Models\DetalleSolicitud;
use App\Models\Estado;
use App\Models\FechaSolicitud;
use App\Models\GrupoAprobado;
use App\Models\SolicitudCurso;
use App\Models\Persona;
use App\Models\SolicitudGrupo;
use App\Models\Usuario;
use App\Models\Carrera;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DocenciaController extends Controller
{
    public function fechaRecepcion(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'anio' => 'required',
                    'ciclo' => 'required',
                    'fecha_inicio' => 'required',
                    'fecha_fin' => 'required'
                ],
                [
                    'anio.required' => 'Es anio no puede estar vacío',
                    'ciclo.required' => 'El ciclo no puede estar vacío',
                    'fecha_inicio.required' => 'Es necesario establecer una fecha de inicio',
                    'fecha_fin.required' => 'Es necesario establecer una fecha de final',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $solicitud_existente = FechaSolicitud::where('anio', $request->anio)->where('ciclo', $request->ciclo)->first();


            if ($solicitud_existente) {
                return response()->json(['Errormessage' => 'Ya se ha establecido una fecha para año y ciclo solicitado please try again'], 400);
            }

            FechaSolicitud::create([
                'anio' => $request->input('anio'),
                'ciclo' => $request->input('ciclo'),
                'fecha_inicio' => $request->input('fecha_inicio'),
                'fecha_fin' => $request->input('fecha_fin'),
            ]);
            return response()->json(['message' => 'Plazo para la recepción de solicutudes de cursos establecida'], 200);
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
                return response()->json(['message' => 'No hay fechas registradas'], 400);
            }

            return response()->json(['Fechas_de_Solictud' => $todasfechas]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    //Obtiene una lista de solicitudes para un lapso establecido de recepción
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

    //Obtiene todas las solicitudes realizadas
    public function Listar_todas_solicitudes(Request $request)
    {
        $solicitudcompleta = [];

        try {
            $solicitudcursos = SolicitudCurso::all();

            if ($solicitudcursos == null) {
                return response()->json(['Message' => 'No hay solicitudes de cursos'], 200);
            }

            foreach ($solicitudcursos as $solicitud) {
                $nombrecarrera = Carrera::where('id', $solicitud->id_carrera)->select('nombre')->first();
                $usuario = Usuario::where('id', $solicitud->id_coordinador)->first();
                $nombrepersona = Persona::where('id', $usuario->id_persona)->select('nombre')->first();
                $estado = Estado::where('id', $solicitud->id_estado)->select('nombre')->first();
                $solicitud->fecha = Carbon::parse($solicitud->fecha)->format('Y-m-d');

                $solicitudarreglo = [
                    'id' => $solicitud->id,
                    'fecha' => $solicitud->fecha,
                    'semestre' => $solicitud->semestre,
                    'carrera' => $nombrecarrera->nombre,
                    'coordinador' => $nombrepersona->nombre,
                    'estado' => $estado->nombre
                ];

                $detalles[] = $solicitudarreglo;

            }

            return response()->json(['Solicitudes_de_curso' => $detalles], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }



    }

    //Cambia estado de una solicitud, o sea (ACEPTADA/RECHADA) 
    public function cambiarEstadoSolicitud(Request $request)
    {
        try {
            $idSolicitud = $request->input('id_solicitud');
            $estadoNombre = $request->input('estado');
            $observacion = $request->input('observacion');

            $solicitudCurso = SolicitudCurso::where('id', $idSolicitud)->first();
            $estado = Estado::where('nombre', $estadoNombre)->first();

            if (!$solicitudCurso) {
                return response()->json(['errorMesage' => 'La solicitud no se encontró en la base de datos'], 400);
            }

            if (!$estado) {
                return response()->json(['errorMesage' => 'El estado no se encontró en la base de datos'], 400);
            }

            $validator = Validator::make($request->all(), [
                'obervacion' => Rule::requiredIf($estadoNombre == 'Rechazado'),
            ], [
                'observacion.required' => 'Si usted rechazó la solicitud, es obligatorio poner una observación del por qué'
            ]);

            if ($validator->fails()) {
                return response()->json(['errorMesage' => $validator->errors()], 400);
            }

            $result = [];
            if ($estadoNombre == 'Aceptado') {
                $solicitudCurso->id_estado = $estado->id;
                $solicitudCurso->save();
                $this->aprobarUnaSolicitud($solicitudCurso, $request->user()->id, $result);

                return response()->json(['success' => true, 'message' => 'Se ha aceptado la solicitud', 'result' => $result], 200);
            } else {
                $solicitudCurso->id_estado = $estado->id;
                $solicitudCurso->observacion = $observacion;
                $solicitudCurso->save();
                return response()->json(['success' => true, 'message' => 'Se ha rechazado la solicitud'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //Cuando se acepta una solicitud se asocia a un encargado y a un registro de aceptacion
    public function aprobarUnaSolicitud($solicitud, $idEncargado, &$result)
    {
        $solicitudAprobada = AprobacionSolicitudCurso::create([
            'id_solicitud' => $solicitud->id,
            'id_encargado' => $idEncargado,

        ]);
        $cursosaceptados = $this->aprobarUnCursoDeUnaSolicitud($solicitudAprobada, $solicitud);
        $result[] = [
            'solicitudAprobada' => $solicitudAprobada,
            'cursosaceptados' => $cursosaceptados
        ];
    }

    //Sirve para aprobar un curso de una solicitud
    public function aprobarUnCursoDeUnaSolicitud($solcitudAprobada, $solicitud)
    {
        $cursoaceptados = [];
        $detalleCurso = DetalleSolicitud::where('id_solicitud', $solicitud->id)->get();

        //para un curso
        foreach ($detalleCurso as $curso) {
            $cursoaceptado = DetalleAprobacionCurso::create([
                'id_solicitud' => $solcitudAprobada->id,
                //Id de la solicitud aprobada
                'id_detalle' => $curso->id,
                //Id de los cursos que estan en la solicitud

            ]);

            $grupoaceptados = $this->aprobarGruposParaUnaSolicitud($cursoaceptado, $curso);
            $cursoaceptados[] = [
                'cursoaceptado' => $cursoaceptado,
                'grupoaceptado' => $grupoaceptados,
            ];
        }
        return $cursoaceptados;
    }

    //Sirve para aprobar los grupos dentro de un curso para una solicitud
    public function aprobarGruposParaUnaSolicitud($cursoaceptado, $curso)
    {
        $grupoaceptados = [];
        $cursogrupo = SolicitudGrupo::where('id_detalle', $curso->id)->get();
        foreach ($cursogrupo as $grupo) {
            $grupoAceptado = GrupoAprobado::create([
                'id_detalle' => $cursoaceptado->id,
                //id del curso que aceptaron
                'id_solicitud' => $grupo->id,
                //id del grupo que estan en la solicitud
            ]);
            $grupoaceptados[] = ['grupoaceptado' => $grupoAceptado];
            $grupoaceptados = array_merge($grupoaceptados, $this->aprobarGruposParaUnaSolicitud($grupoAceptado, $grupo));
        }
        return $grupoaceptados;
    }
}