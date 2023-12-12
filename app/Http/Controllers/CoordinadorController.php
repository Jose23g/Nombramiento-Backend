<?php

namespace App\Http\Controllers;

use App\Models\AprobacionSolicitudCurso;
use App\Models\Carga;
use App\Models\Curso;
use App\Models\DetalleSolicitud;
use App\Models\Estado;
use App\Models\HorariosGrupo;
use App\Models\Persona;
use App\Models\PSeis;
use App\Models\SolicitudCurso;
use App\Models\SolicitudGrupo;
use App\Models\Telefono;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CoordinadorController extends Controller
{

    public function Solicitud_de_curso(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'carrera_id' => 'required',
                'fecha_id' => 'required',
                'carga_total' => 'required',
                'detalle_solicitud' => 'required|array|min:1',
                'detalle_solicitud.*.curso_id' => 'required',
                'detalle_solicitud.*.grupos' => 'required',
                'detalle_solicitud.*.horas_teoricas' => 'required',
                'detalle_solicitud.*.horas_practicas' => 'required',
                'detalle_solicitud.*.horas_laboratorio' => 'required',
                'detalle_solicitud.*.solicitud_grupo' => 'required|array|min:1',
                'detalle_solicitud.*.solicitud_grupo.*.profesor_id' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.carga_id' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.grupo' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.cupo' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.individual_colegiado' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.tutoria' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.horas' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.recinto' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.horario' => 'required|array|min:1',
                'detalle_solicitud.*.solicitud_grupo.*.horario.*.dia_id' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.horario.*.hora_inicio' => 'required',
                'detalle_solicitud.*.solicitud_grupo.*.horario.*.hora_fin' => 'required',
            ],
        );

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $fechaActual = Carbon::now();
            // $fechaSolicitud = FechaSolicitud::where('anio', $request->input('anio'))->where('semestre', $request->input('semestre'))->first();
            // $existesolicitud = SolicitudCurso::where('anio', $request->input('anio'))->where('semestre', $request->input('semestre'))->where('id_carrera', $request->input('id_carrera'))->first();
            // if ($existesolicitud) {
            //     return response()->json([
            //         'error' => 'Ya hay una solicitud que coincide con',
            //         'anio' => $request->anio,
            //         'semestre' => $request->semestre,
            //         'id_carrera' => $request->id_carrera,
            //     ], 400);
            // }
            // if (!$fechaSolicitud || !$fechaActual->between($fechaSolicitud->fecha_inicio, $fechaSolicitud->fecha_fin)) {
            //     return response()->json(['error' => 'El periodo para realizar la solicitud de curso ha finalizado o no esta disponible'], 400);
            // }

            $usuario = $request->user();

            $nuevasolicitud = SolicitudCurso::create([
                'fecha_solicitud_id' => $request->fecha_id,
                'carga_total' => $request->carga_total,
                'coordinador_id' => $usuario->id,
                'carrera_id' => $request->carrera_id,
                'estado_id' => 2,
            ]);



            foreach ($request->detalle_solicitud as $detalle) {

                try {
                    $nuevodetalle = DetalleSolicitud::create([
                        'grupos' => $detalle['grupos'],
                        'horas_teoricas' => $detalle['horas_teoricas'],
                        'horas_practicas' => $detalle['horas_practicas'],
                        'horas_laboratorio' => $detalle['horas_laboratorio'],
                        'solicitud_curso_id' => $nuevasolicitud->id,
                        'curso_id' => $detalle['curso_id'],
                    ]);


                } catch (\Exception $e) {
                    DB::rollback();

                    return response()->json(['message' => $e->getMessage()], 422);
                }
                foreach ($detalle['solicitud_grupo'] as $solicitud_grupo) {
                    // creamos el grupo
                    try {
                        $nuevogrupo = SolicitudGrupo::create([
                            'profesor_id' => $solicitud_grupo['profesor_id'],
                            'grupo' => $solicitud_grupo['grupo'],
                            'cupo' => $solicitud_grupo['cupo'],
                            'carga_id' => $solicitud_grupo['carga_id'],
                            'individual_colegiado' => $solicitud_grupo['individual_colegiado'],
                            'tutoria' => $solicitud_grupo['tutoria'],
                            'horas' => $solicitud_grupo['horas'],
                            'recinto' => $solicitud_grupo['recinto'],
                            'detalle_solicitud_id' => $nuevodetalle->id,
                        ]);


                    } catch (\Exception $e) {
                        DB::rollback();

                        return response()->json(['message' => $e->getMessage()], 422);
                    }

                    foreach ($solicitud_grupo['horario'] as $dias) {
                        // verificamos los dias que vienen para un  grupo y lo agregamos en la base de datos
                        try {
                            $nuevodia = HorariosGrupo::create([
                                'dia_id' => $dias['dia_id'],
                                'solicitud_grupo_id' => $nuevogrupo->id,
                                'hora_inicio' => $dias['hora_inicio'],
                                'hora_fin' => $dias['hora_fin'],
                            ]);

                        } catch (\Exception $e) {
                            DB::rollback();

                            return response()->json(['message' => $e->getMessage()], 422);
                        }
                    }

                }
            }
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => $e->getMessage()], 422);
        }
        DB::commit();

        return response()->json(['message' => 'Se ha creado la solicitud de curso con éxito', 'Solicitud' => $nuevasolicitud], 200);
    }

    public function ultimaSolicitud(Request $request)
    {
        $usuario = $request->user();

        $solicitud = SolicitudCurso::where('id_coordinador', $usuario->id)->orderBy('created_at', 'desc')->first();
        $solicitud->fecha = Carbon::parse($solicitud->fecha)->format('Y-m-d');

        if (!$solicitud) {
            return response()->json(['message' => 'No posee ninguna solicitud'], 200);
        }

        $persona = Persona::where('id', $usuario->id_persona)->first();
        $estado = Estado::find($usuario->id_estado);

        $datos = [
            'id' => $solicitud->id,
            'fecha' => $solicitud->fecha,
            'semestre' => $solicitud->semestre,
            'coordinador' => $persona->nombre,
            'estado' => $estado->nombre,
            'observacion' => $solicitud->observacion,
        ];

        return response()->json($datos, 200);
    }

    public function Editar_solicitud_curso(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id_solicitud',
                'carga_total' => 'required',


            ],
        );

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $detallesrequest = $request->detalle_solicitud;
        $cursos_solicitud_anterior = Detallesolicitud::where('solicitud_curso_id', $request->id_solicitud)->get();

        try {
            if ($detallesrequest) {
                $historialcambiosnuevosdetalles = [];
                $historialcambiosdetallesexistentes = [];
                $historialcambiosdetalleseliminados = [];
                $nuevos = [];
                $detallexistente = [];

                foreach ($detallesrequest as $detallessolicitud) {
                    if (!array_key_exists('id', $detallessolicitud)) {
                        // significa que este detalle es nuevo por ende se puede validar antes de tratar de ingresarlo
                        $validator2 = Validator::make(
                            $detallessolicitud,
                            [
                                'curso_id' => 'required',
                                'grupos' => 'required',
                                'horas_teoricas' => 'required',
                                'horas_practicas' => 'required',
                                'horas_laboratorio' => 'required',
                                'solicitud_grupo' => 'required|array|min:1',
                                'solicitud_grupo.*.profesor_id' => 'required|exists:usuarios,id',
                                'solicitud_grupo.*.carga_id' => 'required',
                                'solicitud_grupo.*.grupo' => 'required',
                                'solicitud_grupo.*.cupo' => 'required',
                                'solicitud_grupo.*.individual_colegiado' => 'required',
                                'solicitud_grupo.*.tutoria' => 'required',
                                'solicitud_grupo.*.horas' => 'required',
                                'solicitud_grupo.*.recinto' => 'required',
                                'solicitud_grupo.*.horario' => 'required|array|min:1',
                                'detalle_solicitud.*.solicitud_grupo.*.horario' => 'required|array|min:1',
                                'solicitud_grupo.*.horario.*.dia_id' => 'required|exists:dias,id',
                                'solicitud_grupo.*.horario.*.hora_inicio' => 'required',
                                'solicitud_grupo.*.horario.*.hora_fin' => 'required'
                            ],
                            [
                                'solicitud_grupo.required' => 'Se ha ingresado un curso sin grupos',
                                'solicitud_grupo.array' => 'El campo solicitud de grupo debe ser un arreglo.',
                                'solicitud_grupo.min' => 'Los cursos deben traer grupos asociados',
                                'solicitud_grupo.*.grupo' => 'Cada grupo tiene que tener su numero',
                                'solicitud_grupo.*.profesor_id.exists' => 'Error al señalar profesor',
                                'solicitud_grupo.*.cupo' => 'El cupo es requerido para cada grupo',
                                'detalle_solicitud.*.solicitud_grupo.*.horario.required' => 'El horario es requerido',
                                'detalle_solicitud.*.solicitud_grupo.*.horario.min' => 'El horario no trae dias',
                            ]
                        );
                        if ($validator2->fails()) {
                            // puedo generar un error general o uno especifico
                            return response()->json(['message' => $validator2->errors()], 400);
                        } else {

                            $nuevos[] = $detallessolicitud; // quiere decir que el detalle es nuevo.

                        }
                    } else {
                        $validarcursoexistente = Validator::make(
                            $detallessolicitud,
                            [
                                'id' => 'required|exists:detalle_solicitudes,id',
                                'curso_id' => 'required',
                                'grupos' => 'required',
                                'horas_teoricas' => 'required',
                                'horas_practicas' => 'required',
                                'horas_laboratorio' => 'required',
                                'solicitud_grupo' => 'required|array|min:1',
                                'solicitud_grupo.*.profesor_id' => 'required|exists:usuarios,id',
                                'solicitud_grupo.*.carga_id' => 'required',
                                'solicitud_grupo.*.grupo' => 'required',
                                'solicitud_grupo.*.cupo' => 'required',
                                'solicitud_grupo.*.individual_colegiado' => 'required',
                                'solicitud_grupo.*.tutoria' => 'required',
                                'solicitud_grupo.*.horas' => 'required',
                                'solicitud_grupo.*.recinto' => 'required',
                                'solicitud_grupo.*.horario' => 'required|array|min:1',
                                'detalle_solicitud.*.solicitud_grupo.*.horario' => 'required|array|min:1',
                                'solicitud_grupo.*.horario.*.dia_id' => 'required|exists:dias,id',
                                'solicitud_grupo.*.horario.*.hora_inicio' => 'required',
                                'solicitud_grupo.*.horario.*.hora_fin' => 'required'
                            ],
                            [
                                'id.required' => 'Revise los detalles adjuntados',
                                'id.exists' => 'Error detalles invalidos',
                                'solicitud_grupo.required' => 'Se ha ingresado un curso sin grupos',
                                'solicitud_grupo.array' => 'El campo solicitud de grupo debe ser un arreglo.',
                                'solicitud_grupo.min' => 'Los cursos deben traer grupos asociados',
                                'solicitud_grupo.*.grupo' => 'Cada grupo tiene que tener su numero',
                                'solicitud_grupo.*.profesor_id.exists' => 'Error al señalar profesor',
                                'solicitud_grupo.*.cupo' => 'El cupo es requerido para cada grupo',
                                'detalle_solicitud.*.solicitud_grupo.*.horario.required' => 'El horario es requerido',
                                'detalle_solicitud.*.solicitud_grupo.*.horario.min' => 'El horario no trae dias',
                            ]
                        );
                        if ($validarcursoexistente->fails()) {
                            // puedo generar un error general o uno especifico
                            return response()->json(['message' => $validarcursoexistente->errors()], 400);
                        } else {
                            $detallexistente[] = $detallessolicitud; // quiere decir que el detalle es nuevo.

                        }
                    }
                }
                // si vienen crusos nuevos se agregan diurectamente a la base de datos
                if ($nuevos) {
                    try {
                        echo 'vienen nuevos los agregamos';
                        $agregarnuevodetalle = $this->Ingresar_nuevos_detalle($nuevos, $request->id_solicitud);
                        $historialcambiosnuevosdetalles[] = $agregarnuevodetalle;
                    } catch (\Exception $e) {
                        return response(['error' => $e->getMessage()], 400);
                    }
                }

                $nuevalistacursos = $detallexistente;

                // iniciamos el bloque try
                // si vienen que ya estaban en la bd
                if ($nuevalistacursos) {
                    foreach ($cursos_solicitud_anterior as $cursoanterior) {
                        if (!in_array($cursoanterior->id, array_column($nuevalistacursos, 'id'), true)) {
                            try {
                                $eliminardetalle = $this->Eliminaciones_editar_solicitud($cursoanterior->id, 1); // si no esta el curso manda a borrarlo a la base de datos
                                // Ingresar al registro de acciones
                                $historialcambiosdetalleseliminados[] = $eliminardetalle;
                            } catch (\Exception $e) {
                                return response()->json(['error' => $e->getMessage()], 400);
                            }

                        } else { //sabemos que si existe y viene dentro del nuevo request
                            $cursodetalle = Curso::where('id', $cursoanterior->curso_id)->first();
                            $grupos_cruso_anterior = SolicitudGrupo::where('detalle_solicitud_id', $cursoanterior->id)->get();
                            $historialcambiosdetalle = [];
                            // se procede a verificar la existencia de un detalle que coincideda con el anterior de la base de datos
                            $detallenuevo = current(array_filter($nuevalistacursos, fn($detalle) => $detalle['id'] == $cursoanterior->id));
                            // quiere decir debido a que este detalle esta dentro de la bd y dentro de la nueva lista entonces vamos a compararlos a ver que pedo
                            if ($detallenuevo) {

                                //actualizamos los datos de ser cambiados del detalle actual en la base de datos acorbde a lo que viene
                                $detalleactualizar = Detallesolicitud::find($cursoanterior->id);
                                $detalleactualizar->grupos = $detallenuevo['grupos'];
                                $detalleactualizar->horas_teoricas = $detallenuevo['horas_teoricas'];
                                $detalleactualizar->horas_practicas = $detallenuevo['horas_practicas'];
                                $detalleactualizar->horas_laboratorio = $detallenuevo['horas_laboratorio'];
                                $detalleactualizar->save();

                                $gruposnuevos = []; // va almacenaar los grupos nuevos que vienen del request  para un detalle que existe en la bd
                                $grupoexistente = []; // va almacenaar los modificados para un detalle que existe en la bd
                                // Recorremos todos los grupos que viene del request para almacenarlos en su arreglo debido
                                foreach ($detallenuevo['solicitud_grupo'] as $gruposrequest) {
                                    if (!array_key_exists('id', $gruposrequest)) {
                                        $gruposnuevos[] = $gruposrequest; // quiere decir que tiene grupos nuevos que deben ingresarse a la bd
                                    } else {
                                        $grupoexistente[] = $gruposrequest; // quiere decir que estos vienen para actualizar
                                    }
                                }

                                if ($gruposnuevos) {
                                    try {

                                        $nuevogrupo = $this->Añadir_grupo($cursoanterior->id, $gruposnuevos); // añadimos a los grupos nuevos del detalle
                                        // Ingresar al registro de acciones
                                        $historialcambiosdetalle[] = $nuevogrupo;
                                    } catch (\Exception $e) {
                                        return response()->json(['error' => $e->getMessage()], 400);
                                    }
                                }

                                foreach ($grupos_cruso_anterior as $grupo) {
                                    // verificamos si el grupo que esta en la base de datos no viene de la nueva lista de grupos asociados a un detalle para namdarlo a eliminar
                                    if (!in_array($grupo->id, array_column($grupoexistente, 'id'), true)) {
                                        // como no viene llamamos al metoso eliminar y le mandamos el id_referencia de grupo y la acion 2 que elimina grupo y horario de la base de datos
                                        try {
                                            $grupoeliminar = $this->Eliminaciones_editar_solicitud($grupo->id, 2);

                                            // ingresar al registro de acciones
                                            $historialcambiosdetalle[] = $grupoeliminar;
                                        } catch (\Exception $e) {
                                            return response()->json(['error' => $e->getMessage()], 400);
                                        }
                                    } else {
                                        // como si viene el grupo se procede a editar en base a lo que viene de la bd
                                        $grupoaeditar = current(array_filter($grupoexistente, fn($grupoe) => $grupoe['id'] == $grupo->id)); // busco el grupo a editar en el request

                                        if ($grupoaeditar) { // if solo para validarsh
                                            try {
                                                $grupoactualizar = $this->Actualizar_grupo($grupo->id, $grupoaeditar); // manda a actualizar segun venga el metodo
                                                $historialcambiosdetalle[] = $grupoactualizar;
                                            } catch (\Exception $e) {
                                                return response()->json(['error' => $e->getMessage()], 400);
                                            }
                                        }
                                    }
                                }
                            }

                            $cambioshechosaldetalle = [
                                'curso' => $cursodetalle->nombre,
                                'cambios realizados' => $historialcambiosdetalle,
                            ];
                            // agrego los cambios del detalle al historial general
                            $historialcambiosdetallesexistentes[] = $cambioshechosaldetalle;
                        }
                    }
                } else { // si no viene cursos ya existentes dentro de la bd se mandan a eliminar
                    foreach ($cursos_solicitud_anterior as $detalleeliminar) {
                        try {
                            $eliminardetallebd = $this->Eliminaciones_editar_solicitud($detalleeliminar->id, 1);
                            $historialcambiosdetalleseliminados[] = $eliminardetallebd;
                        } catch (\Exception $e) {
                            return response()->json(['error' => $e->getMessage()], 400);
                        }
                    }
                }

                // retorno la respuesta con todo el resumen realizado
            } else { // Si no viene una lista de cursos en la base de datos se elimina toda la solicitud
                try {
                    // eliminamos todos los registros de los detalles
                    foreach ($cursos_solicitud_anterior as $detalleeliminar) {
                        $this->Eliminaciones_editar_solicitud($detalleeliminar->id, 1);
                    }
                    // Ahora eliminamos la solicitud ya que al estar vacía no tiene sentido tenerla sin nada

                    SolicitudCurso::where('id', $request->id_solicitud)->delete();

                    return response()->json(['message' => 'Se han eliminado los datos y la solicitud'], 200);
                } catch (\Exception $e) {
                    return response()->json(['error' => $e->getMessage()], 422);
                }
            }

            // retorno de estado del metodo
            return response()->json([
                'la solicitud ha sido procesada',
                'Cursos agregados' => $historialcambiosnuevosdetalles,
                'Cursos actualizados' => $historialcambiosdetallesexistentes,
                'Cursos eliminados' => $historialcambiosdetalleseliminados
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function Ingresar_nuevos_detalle($listadetallesnueva, $id_solicitud)
    {
        try {

            $detallesagregrados = [];

            foreach ($listadetallesnueva as $deta) {

                //creamos el nuevo detalle segun los nuevos detalles del request
                $nuevodetallesolicitud = DetalleSolicitud::create([
                    'grupos' => $deta['grupos'],
                    'horas_teoricas' => $deta['horas_teoricas'],
                    'horas_practicas' => $deta['horas_practicas'],
                    'horas_laboratorio' => $deta['horas_laboratorio'],
                    'solicitud_curso_id' => $id_solicitud,
                    'curso_id' => $deta['curso_id'],
                ]);
                $nombrecurso = Curso::where('id', $nuevodetallesolicitud->curso_id)->first();
                // ahora recorremos el arreglo de los grupos del detalle
                foreach ($deta['solicitud_grupo'] as $lineanuevogrupo) {

                    //creamos el grupo
                    $nuevogrupo = SolicitudGrupo::create([
                        'profesor_id' => $lineanuevogrupo['profesor_id'],
                        'grupo' => $lineanuevogrupo['grupo'],
                        'cupo' => $lineanuevogrupo['cupo'],
                        'carga_id' => $lineanuevogrupo['carga_id'],
                        'individual_colegiado' => $lineanuevogrupo['individual_colegiado'],
                        'tutoria' => $lineanuevogrupo['tutoria'],
                        'horas' => $lineanuevogrupo['horas'],
                        'recinto' => $lineanuevogrupo['recinto'],
                        'detalle_solicitud_id' => $nuevodetallesolicitud->id,
                    ]);
                    foreach ($lineanuevogrupo['horario'] as $dias) {
                        $nuevodia = HorariosGrupo::create([
                            'dia_id' => $dias['dia_id'],
                            'solicitud_grupo_id' => $nuevogrupo->id,
                            'hora_inicio' => $dias['hora_inicio'],
                            'hora_fin' => $dias['hora_fin'],
                        ]);
                    }
                }
                $detallesagregrados[] = $nombrecurso->nombre;
            }



            return [' Se agregaron los cursos ' => $detallesagregrados];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function Eliminaciones_editar_solicitud($id_detalle, $accion)
    {
        try {
            switch ($accion) {
                case 1: // Case para eliminar curso,grupo,horarios y dia
                    try {

                        $grupos = SolicitudGrupo::where('detalle_solicitud_id', $id_detalle)->get();
                        $buscardetalle = DetalleSolicitud::where('id', $id_detalle)->first();
                        $cursoeliminado = Curso::where('id', $buscardetalle->curso_id)->first();

                        foreach ($grupos as $grupo) {
                            // eliminamos los dias y luego el grupo asociado
                            HorariosGrupo::where('solicitud_grupo_id', $grupo->id)->delete();
                            SolicitudGrupo::where('id', $grupo->id)->delete();
                            // una vez eliminado el grupo eliminamos los dias y el horario asignado al grupo
                        }
                        DetalleSolicitud::where('id', $id_detalle)->delete();

                        return [' se ha eliminado el curso ' => $cursoeliminado->nombre];
                    } catch (\Exception $e) {
                        throw $e;
                    }

                    break;

                case 2:
                    try {
                        $grupoeliminar = SolicitudGrupo::Where('id', $id_detalle)->first();
                        SolicitudGrupo::Where('id', $id_detalle)->delete();
                        HorariosGrupo::where('solicitud_grupo_id', $id_detalle)->delete();

                        return [' se ha eliminado el grupo ' => $grupoeliminar->grupo];
                    } catch (\Exception $e) {
                        throw $e;
                    }

                    break;
                default:
                    // Maneja una opción no válida
                    break;
            }
        } catch (\Exception $e) {
            return response()->json(['Message' => $e->getMessage()], 500);
        }
    }

    public function Añadir_grupo($id_detalle, $grupo)
    {
        try {
            $idcurso = DetalleSolicitud::find($id_detalle);
            $nombrecurso = Curso::where('id', $idcurso->curso_id)->first();

            foreach ($grupo as $nuevogrupo) {

                $nuevogrupo = SolicitudGrupo::create([
                    'profesor_id' => $nuevogrupo['profesor_id'],
                    'grupo' => $nuevogrupo['grupo'],
                    'cupo' => $nuevogrupo['cupo'],
                    'carga_id' => $nuevogrupo['carga_id'],
                    'individual_colegiado' => $nuevogrupo['individual_colegiado'],
                    'tutoria' => $nuevogrupo['tutoria'],
                    'horas' => $nuevogrupo['horas'],
                    'recinto' => $nuevogrupo['recinto'],
                    'detalle_solicitud_id' => $id_detalle,
                ]);

                // ahora agregamos el nuevo horario
                foreach ($nuevogrupo['horario'] as $dias) {

                    $nuevodia = HorariosGrupo::create([
                        'dia_id' => $dias['dia_id'],
                        'solicitud_grupo_id' => $nuevogrupo->id,
                        'hora_inicio' => $dias['hora_inicio'],
                        'hora_fin' => $dias['hora_fin'],
                    ]);
                }

            }

            return [' se añadio el grupo ' => $nuevogrupo->grupo];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function Actualizar_grupo($idgrupobd, $gruporequest)
    {

        try {
            // fragmento para editar un grupo
            $grupoeditar = SolicitudGrupo::find($idgrupobd);
            $grupoeditar->grupo = $gruporequest['grupo'];
            $grupoeditar->cupo = $gruporequest['cupo'];
            $grupoeditar->profesor_id = $gruporequest['profesor_id'];
            $grupoeditar->carga_id = $gruporequest['carga_id'];
            $grupoeditar->individual_colegiado = $gruporequest['individual_colegiado'];
            $grupoeditar->tutoria = $gruporequest['tutoria'];
            $grupoeditar->horas = $gruporequest['horas'];
            $grupoeditar->recinto = $gruporequest['recinto'];

            //vamos a editar el horario eliminando los dias que hay y agregando los nuevos sin alterar el id del horario
            HorariosGrupo::where('solicitud_grupo_id', $idgrupobd)->delete();

            // recorremos los nuevos dias de su grupo
            foreach ($gruporequest['horario'] as $dias) {
                //añadimos los nuevos dia con su referencia apropiada
                $nuevodia = HorariosGrupo::create([
                    'dia_id' => $dias['dia_id'],
                    'solicitud_grupo_id' => $idgrupobd,
                    'hora_inicio' => $dias['hora_inicio'],
                    'hora_fin' => $dias['hora_fin'],
                ]);
            }
            $grupoeditar->save();

            return [' se actualizo el grupo ' => $grupoeditar->grupo];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function obtenerProfesoresdeUltimaSolicitud(Request $request)
    {
        try {
            $carreras = $request->user()->carreras;
            $profesores = [];

            foreach ($carreras as $carrera) {
                $solicitudes = $this->obtenerUltimaSolicitudAprobada($carrera->id);

                foreach ($solicitudes as $solicitudID) {

                    //return response()->json($solicitudID->solicitud_curso_id,200);
                    $profesores[] = $this->obtenerprofesores($solicitudID->solicitud_curso_id);
                }
            }
            //$profesores = array_unique($profesores);
            $profesores = collect($profesores);
            $profesores = $profesores->collapse();
            return response()->json($profesores, 200);

        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function obtenerUltimaSolicitudAprobada($carreraID)
    {
        $solicitudID = AprobacionSolicitudCurso::where('carrera_id', $carreraID)->latest()->get('solicitud_curso_id');
        // $solicitudID = SolicitudCurso::where('carrera_id', $carreraID)->latest()->first('id');


        if ($solicitudID) {
            return $solicitudID;
        }
        return throw new \Exception('No se ha aprobado una solicitud aun');

    }

    public function obtenerprofesores($solicitudID)
    {

        $resultados = SolicitudGrupo::join('detalle_solicitudes as ds', 'solicitud_grupos.detalle_solicitud_id', '=', 'ds.id')
            ->join('usuarios as us', 'solicitud_grupos.profesor_id', '=', 'us.id')
            ->join('personas as pe', 'us.persona_id', '=', 'pe.id')
            ->where('ds.solicitud_curso_id', $solicitudID)
            ->select('profesor_id', 'pe.nombre', 'ds.solicitud_curso_id')
            ->distinct()
            ->get();

        return $resultados;
    }

    public function generarP6(Request $request)
    {
        $validaciones = Validator::make(
            [
                'cargo_categoria' => 'required',
                'profesor_id' => 'required',
                'vig_desde' => 'required',
                'vig_hasta' => 'required',
                'jornada' => 'required',
            ],
            [
                'cargo_categoria.required' => "Es necesario ingresar el cargo o categoria al que está asignado",
                'vig_desde.required' => "No se puede generar el borrador sin haber establecido el inicio de la vigencia",
                'vig_hasta.required' => "No se puede generar el borrador sin haber establecido el final de la vigencia",
                'jornada.required' => "Es necesario ingresar la jornada",
            ]
        );
        if ($validaciones->fails()) {
            return response()->json(['errormessage' => $validaciones->errors()], 422);
        }

        $p6 = PSeis::create([
            'profesor_id' => $request->input('profesor_id'),
            'jornada_id' => $request->input('jornada_id'),
            'fecha_inicio' => $request->input('vig_desde'),
            'fecha_fin' => $request->input('vig_hasta'),
            'cargo_categoria' => $request->input('cargo_categoria'),
        ]);

        return response()->json('P6 generada', 200);
    }

    public function previsualizarP6(Request $request)
    {
        try {


            $profesor_user = Usuario::find($request->input('profesor_id'));
            $profesor = Usuario::find($request->input('profesor_id'))->persona;
            $provincia = Usuario::find($request->input('profesor_id'))->persona->provincia->nombre;
            $canton = Usuario::find($request->input('profesor_id'))->persona->canton->nombre;
            $telefonos = Telefono::where('persona_id', $profesor_user->id)->first();
            $cursosID = $this->obtenerCursosdelProfesor($request->input('solicitud_id'), $profesor_user->id);
            $cursosInf = [];

            foreach ($cursosID as $key) {
                $cursoInfo = Curso::find($key->curso_id);
                $carga = Carga::find($key->carga_id);

                if ($cursoInfo) {
                    $cursosInf[] = [
                        'curso_id' => $cursoInfo->id,
                        'codigo' => $cursoInfo->sigla,
                        'nombre_del_curso' => $cursoInfo->nombre,
                        'carga' => $carga->nombre,
                        'grupo_id' => $key
                    ];
                }
            }

           
            $borradorP6 = [
                'profesor_id' => $profesor_user->id,
                'nombre' => $profesor->nombre,
                'cedula' => $profesor->cedula,
                'correo' => $profesor_user->correo,
                'otroCorreo' => $profesor->otro_correo,
                'provincia' => $provincia,
                'canton' => $canton,
                'telefonos' => [
                    'personal' => $telefonos->personal,
                    'trabajo' => $telefonos->trabajo,
                ],
                'cursos' => $cursosInf,
            ];
           
            return response()->json($borradorP6, 200);

        } catch (\Exception $e) {
            return response()->json(['errormessage' => $e->getMessage()], 500);
        }
    }

    public function obtenerCursosdelProfesor($solicitudID, $profesorID)
    {
        $cursos = DetalleSolicitud::join('solicitud_grupos', 'detalle_solicitudes.id', '=', 'solicitud_grupos.detalle_solicitud_id')
            ->where('solicitud_grupos.profesor_id', $profesorID)
            ->where('detalle_solicitudes.solicitud_curso_id', $solicitudID)
            ->select(
                'detalle_solicitudes.solicitud_curso_id',
                'solicitud_grupos.detalle_solicitud_id',
                'solicitud_grupos.id',
                'solicitud_grupos.profesor_id',
                'detalle_solicitudes.curso_id',
                'solicitud_grupos.carga_id',
            )
            ->get();
        return $cursos;
    }
}
