<?php

namespace App\Http\Controllers;

use App\Models\AprobacionSolicitudCurso;
use App\Models\Carrera;
use App\Models\DetalleAprobacionCurso;
use App\Models\DetalleSolicitud;
use App\Models\Estado;
use App\Models\Fecha;
use App\Models\GrupoAprobado;
use App\Models\Persona;
use App\Models\SolicitudCurso;
use App\Models\SolicitudGrupo;
use App\Models\Tipos;
use App\Models\Usuario;
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
                    'fecha_fin' => 'required',
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

            $solicitud_existente = Fecha::where('anio', $request->anio)->where('ciclo', $request->ciclo)->first();

            if ($solicitud_existente) {
                return response()->json(['Errormessage' => 'Ya se ha establecido una fecha para año y ciclo solicitado please try again'], 400);
            }

            Fecha::create([
                'anio' => $request->input('anio'),
                'ciclo' => $request->input('ciclo'),
                'tipo_id' => 1,
                'fecha_inicio' => $request->input('fecha_inicio'),
                'fecha_fin' => $request->input('fecha_fin'),
            ]);

            return response()->json(['message' => 'Plazo para la recepción de solicutudes de cursos establecida'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function comprobarFechaRecepcion(Request $request)
    {
        $fechaActual = Carbon::now();
        $fecha = Fecha::where('anio', $request->input('anio'))->where('semestre', $request->input('semestre'))->first();

        if (!$fecha || !$fechaActual->between($fecha->fecha_inicio, $fecha->fecha_fin)) {
            return response()->json(['error' => 'El periodo para realizar la solicitud de curso ha finalizado o no está disponible'], 400);
        }

        return response()->json(['messaje' => 'se puede hacer la solicitud'], 200);
    }

    public function Listar_fechas_solicitudes(Request $request)
    {
        try {
            $todasfechas = Fecha::all();
            if (!$todasfechas) {
                return response()->json(['message' => 'No hay fechas registradas'], 400);
            }

            return response()->json(['Fechas_de_Solictud' => $todasfechas]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Obtiene una lista de solicitudes para un lapso establecido de recepción
    public function Ver_Solicitud_curso_fecha(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $verificarfecha = Fecha::Where('id', $request->fecha_id)->first();

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
                'solicitudes' => $solicitudcursos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Obtiene todas las solicitudes realizadas
    public function Listar_todas_solicitudes(Request $request)
    {
        $solicitudcompleta = [];

        try {
            $consutaestados = app()->make(EstadosController::class);

            $categoria = $consutaestados->Estado_por_nombre('pendiente');

            $solicitudcursos = SolicitudCurso::where('estado_id', $categoria)->get();

            if ($solicitudcursos == null) {
                return response()->json(['Message' => 'No hay solicitudes de cursos'], 200);
            }

            foreach ($solicitudcursos as $solicitud) {
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

    // Cambia estado de una solicitud, o sea (ACEPTADA/RECHADA)
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
                'observacion.required' => 'Si usted rechazó la solicitud, es obligatorio poner una observación del por qué',
            ]);

            if ($validator->fails()) {
                return response()->json(['errorMesage' => $validator->errors()], 400);
            }

            $result = [];
            if ($estadoNombre == 'Aceptado') {
                $solicitudCurso->estado_id = $estado->id;
                $solicitudCurso->save();
                $this->aprobarUnaSolicitud($solicitudCurso, $request->user()->id, $result);

                return response()->json(['success' => true, 'message' => 'Se ha aceptado la solicitud', 'result' => $result], 200);
            } else {
                $solicitudCurso->estado_id = $estado->id;
                $solicitudCurso->observacion = $observacion;
                $solicitudCurso->save();

                return response()->json(['success' => true, 'message' => 'Se ha rechazado la solicitud'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //Cuando se acepta una solicitud se asocia a un encargado, a un registro de aceptacion y una carrrera.
    public function aprobarUnaSolicitud($solicitud, $idEncargado, &$result)
    {
        $solicitudAprobada = AprobacionSolicitudCurso::create([
            'solicitud_curso_id' => $solicitud->id,
            'encargado_id' => $idEncargado,
        ]);
        $cursosaceptados = $this->aprobarUnCursoDeUnaSolicitud($solicitudAprobada, $solicitud);
        $result[] = [
            'solicitudAprobada' => $solicitudAprobada,
            'cursosaceptados' => $cursosaceptados,
        ];
    }

    // Sirve para aprobar un curso de una solicitud
    public function aprobarUnCursoDeUnaSolicitud($solcitudAprobada, $solicitud)
    {
        $cursoaceptados = [];
        $detalleCurso = DetalleSolicitud::where('solicitud_curso_id', $solicitud->id)->get();


        //para un curso
        foreach ($detalleCurso as $solicitudDetalle) {
            $detalleAprobado = DetalleAprobacionCurso::create([
                'solicitud_aprobada_id' => $solcitudAprobada->id,
                //Id de la solicitud aprobada
                'detalle_solicitud_id' => $solicitudDetalle->id,
                //Id del detalle que se aprueba

            ]);

            $grupoaceptados = $this->aprobarGruposParaUnaSolicitud($detalleAprobado, $solicitudDetalle);
            $cursoaceptados[] = [
                'cursoaceptado' => $detalleAprobado,
                'grupoaceptado' => $grupoaceptados,
            ];
        }

        return $cursoaceptados;
    }

    //Sirve para aprobar los grupos dentro de un curso para una solicitud
    public function aprobarGruposParaUnaSolicitud($detalleAprobado, $detalleSolicitud)
    {
        $grupoaceptados = [];
        $cursogrupo = SolicitudGrupo::where('detalle_solicitud_id', $detalleSolicitud->id)->get();
        foreach ($cursogrupo as $grupo) {
            $grupoAceptado = GrupoAprobado::create([

                'detalle_aprobado_id' => $detalleAprobado->id,
                //id del curso que aceptaron
                'solicitud_grupo_id' => $grupo->id,
                //id del grupo que estan en la solicitud
            ]);
            $grupoaceptados[] = ['grupoaceptado' => $grupoAceptado];
            // $grupoaceptados = array_merge($grupoaceptados, $this->aprobarGruposParaUnaSolicitud($grupoAceptado, $grupo));
        }

        return $grupoaceptados;
    }

    public function Obtener_ultima_fecha(Request $request)
    {
        try {
            $ultimafecha = Fecha::orderBy('created_at', 'desc')->first();

            if (!$ultimafecha) {
                return response()->json(['message' => 'no hay fechas ingresadas'], 200);
            }

            return response()->json([
                'anio' => $ultimafecha->anio,
                'semestre' => $ultimafecha->ciclo,
                'fecha_inicio' => $ultimafecha->fecha_inicio,
                'fecha_fin' => $ultimafecha->fecha_fin,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    //Sirve para que docencia pueda esteblecer la vigencia de las p6
    public function establecer_TNombramiento_vigenciaP6(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'anio' => 'required',
                    'ciclo' => 'required',
                    'fecha_inicio' => 'required', //-->desde
                    'fecha_fin' => 'required', //-->hasta
                    'tipo' => 'required', //-->Nombre del tipo de nombramiento
                ],
                [
                    'anio.required' => 'Es necesario añadir el año',
                    'ciclo.required' => 'Es necesario definir para que ciclo',
                    'fecha_inicio.required' => 'Es necesario la fecha de inicio',
                    'fecha_fin.required' => 'Es necesario establecer la fecha limite',
                    'tipo.required' => 'Es necesario para definir una fecha al tipo de nombramiento'
                ]
            );
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
            $tipo = Tipos::where("nombre", $request->tipo)->first();

            //En caso de que el registro <<Tipo Nombramiento>> no exista se crea
            if (!$tipo) {
                $tipo = Tipos::Create([
                    'nombre' => $request->tipo,
                ]);
            }

            $fecha = Fecha::where('tipo_id', $tipo->id)->where('anio', $request->anio)->where('ciclo', $request->ciclo)->first();

            if ($fecha) {
                return response()->json(['error' => 'Ya se han establecido fechas para el tipo de nombramiento '. $tipo->nombre.' para el año ' . $fecha->anio . ', ciclo ' . $fecha->ciclo . ' que van desde ' . $fecha->fecha_inicio . ', hasta ' . $fecha->fecha_fin], 400);
            }

            $fecha = Fecha::Create([
                'tipo_id' => $tipo->id,
                'anio' => $request->anio,
                'ciclo' => $request->ciclo,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
            ]);
            return response()->json(['Se ha establecido fechas para el tipo de nombramiento '. $tipo->nombre.' para el año ' . $fecha->anio . ', ciclo ' . $fecha->ciclo . ' que van desde ' . $fecha->fecha_inicio . ', hasta ' . $fecha->fecha_fin], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    public function obtener_TNombramiento_vigenciaP6(Request $request){
        try{
            $anio = Carbon::now()->year;
            $fecha= Tipos::join('fechas' , 'tipos.id', '=', 'fechas.tipo_id')
            ->whereIn('tipos.nombre',['Continuidad','Interino'])
            ->where('fechas.anio', $anio)->select(['fechas.id','tipos.nombre','anio','ciclo', 'fecha_inicio', 'fecha_fin'])->get();

            return response()->json($fecha);
        }catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
