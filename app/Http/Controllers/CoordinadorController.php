<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\DetalleSolicitud;
use App\Models\Dias;
use App\Models\Estado;
use App\Models\FechaSolicitud;
use App\Models\Horario;
use App\Models\Persona;
use App\Models\SolicitudCurso;
use App\Models\SolicitudGrupo;
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
                'detalle_solicitud.*.solicitud_grupo.*.horario.*.salida' => 'required',
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
                    'id_carrera' => $request->id_carrera,
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
                'fecha' => Carbon::now()->format('Y-m-d'),
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
                } catch (\Exception $e) {
                    DB::rollback();

                    return response()->json(['message' => $e->getMessage()], 422);
                }
                foreach ($detalle['solicitud_grupo'] as $solicitud_grupo) {
                    // primero añadimos el horario general con su tipo
                    try {
                        $nuevohorario = Horario::create([
                            'tipo' => 'SolicitudGrupo',
                        ]);
                    } catch (\Exception $e) {
                        DB::rollback();

                        return response()->json(['message' => $e->getMessage()], 422);
                    }

                    foreach ($solicitud_grupo['horario'] as $dias) {
                        // verificamos los dias que viene dentro del arreglo de horarios para cada curso
                        try {
                            $nuevodia = Dias::create([
                                'id_dia' => $dias['id_dia'],
                                'entrada' => $dias['entrada'],
                                'salida' => $dias['salida'],
                                'id_horario' => $nuevohorario->id,
                            ]);
                        } catch (\Exception $e) {
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
                            'id_horario' => $nuevohorario->id,
                        ]);
                    } catch (\Exception $e) {
                        DB::rollback();

                        return response()->json(['message' => $e->getMessage()], 422);
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
                'anio' => 'required',
                'semestre' => 'required',
                'id_carrera' => 'required',
                'fecha' => 'required|date',
            ],
        );

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $detallesrequest = $request->detalle_solicitud;
        $cursos_solicitud_anterior = Detallesolicitud::where('id_solicitud', $request->id_solicitud)->get();

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
                                'ciclo' => 'required',
                                'grupos' => 'required',
                                'recinto' => 'required',
                                'carga' => 'required',
                                'id_curso' => 'required|exists:cursos,id',
                                'solicitud_grupo' => 'required|array|min:1',
                                'solicitud_grupo.*.id_profesor' => 'required|exists:usuarios,id',
                                'solicitud_grupo.*.grupo' => 'required',
                                'solicitud_grupo.*.cupo' => 'required',
                                'solicitud_grupo.*.horario' => 'required|array|min:1',
                                'detalle_solicitud.*.solicitud_grupo.*.horario' => 'required|array|min:1',
                                'solicitud_grupo.*.horario.*.id_dia' => 'required|exists:dia,id',
                                'solicitud_grupo.*.horario.*.entrada' => 'required',
                                'solicitud_grupo.*.horario.*.salida' => 'required',
                            ],
                            [
                                'solicitud_grupo.required' => 'Se ha ingresado un curso sin grupos',
                                'solicitud_grupo.array' => 'El campo solicitud de grupo debe ser un arreglo.',
                                'solicitud_grupo.min' => 'Los cursos deben traer grupos asociados',
                                'solicitud_grupo.*.grupo' => 'Cada grupo tiene que tener su numero',
                                'solicitud_grupo.*.id_profesor.exists' => 'Error al señalar profesor',
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
                                'ciclo' => 'required',
                                'grupos' => 'required',
                                'recinto' => 'required',
                                'carga' => 'required',
                                'id_curso' => 'required|exists:cursos,id',
                                'solicitud_grupo' => 'required|array|min:1',
                                'solicitud_grupo.*.id_profesor' => 'required|exists:usuarios,id',
                                'solicitud_grupo.*.grupo' => 'required',
                                'solicitud_grupo.*.cupo' => 'required',
                                'solicitud_grupo.*.horario' => 'required|array|min:1',
                                'detalle_solicitud.*.solicitud_grupo.*.horario' => 'required|array|min:1',
                                'solicitud_grupo.*.horario.*.id_dia' => 'required|exists:dia,id',
                                'solicitud_grupo.*.horario.*.entrada' => 'required',
                                'solicitud_grupo.*.horario.*.salida' => 'required',
                            ],
                            [
                                'id.required' => 'Revise los detalles adjuntados',
                                'id.exists' => 'Error detalles invalidos',
                                'solicitud_grupo.required' => 'Se ha ingresado un curso sin grupos',
                                'solicitud_grupo.array' => 'El campo solicitud de grupo debe ser un arreglo.',
                                'solicitud_grupo.min' => 'Los cursos deben traer grupos asociados',
                                'solicitud_grupo.*.grupo' => 'Cada grupo tiene que tener su numero',
                                'solicitud_grupo.*.id_profesor.exists' => 'Error al señalar profesor',
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
                        } else { // sabemos que si existe y viene dentro del nuevo request
                            $cursodetalle = Curso::where('id', $cursoanterior->id_curso)->first();
                            $grupos_cruso_anterior = SolicitudGrupo::where('id_detalle', $cursoanterior->id)->get();
                            $historialcambiosdetalle = [];
                            // se procede a verificar la existencia de un detalle que coincideda con el anterior de la base de datos
                            $detallenuevo = current(array_filter($nuevalistacursos, fn ($detalle) => $detalle['id'] == $cursoanterior->id));
                            // quiere decir debido a que este detalle esta dentro de la bd y dentro de la nueva lista entonces vamos a compararlos a ver que pedo
                            if ($detallenuevo) {
                                // actualizamos los datos de ser cambiados del detalle actual en la base de datos acorbde a lo que viene
                                $detalleactualizar = Detallesolicitud::find($cursoanterior->id);
                                $detalleactualizar->grupos = $detallenuevo['grupos'];
                                $detalleactualizar->carga = $detallenuevo['carga'];
                                $detalleactualizar->recinto = $detallenuevo['recinto'];
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
                                        $grupoaeditar = current(array_filter($grupoexistente, fn ($grupoe) => $grupoe['id'] == $grupo->id)); // busco el grupo a editar en el request

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
            return response()->json(['la solicitud ha sido procesada',
             'Cursos agregados' => $historialcambiosnuevosdetalles,
             'Cursos actualizados' => $historialcambiosdetallesexistentes,
             'Cursos eliminados' => $historialcambiosdetalleseliminados], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function Ingresar_nuevos_detalle($listadetallesnueva, $id_solicitud)
    {
        try {
            $detallesagregrados = [];
            foreach ($listadetallesnueva as $deta) {
                // creamos el nuevo detalle segun los nuevos detalles del request
                $nuevodetallesolicitud = DetalleSolicitud::create([
                    'ciclo' => $deta['ciclo'],
                    'grupos' => $deta['grupos'],
                    'recinto' => $deta['recinto'],
                    'carga' => $deta['carga'],
                    'id_solicitud' => $id_solicitud,
                    'id_curso' => $deta['id_curso'],
                ]);
                $nombrecurso = Curso::where('id', $deta['id_curso'])->first();

                // ahora recorremos el arreglo de los grupos del detalle
                foreach ($deta['solicitud_grupo'] as $nuevogrupo) {
                    // Primeramente recorremos los dias del grupo en donde creamos el horario y añadimos referencia
                    $nuevohorario = Horario::create([
                        'tipo' => 'SolicitudGrupo',
                    ]);

                    foreach ($nuevogrupo['horario'] as $dias) {
                        $nuevodiahorario = Dias::create([
                            'id_dia' => $dias['id_dia'],
                            'entrada' => $dias['entrada'],
                            'salida' => $dias['salida'],
                            'id_horario' => $nuevohorario->id,
                        ]);
                    }
                    // una vez creado el horario procedemos a añadir el grupo
                    $nuevalineacurso = SolicitudGrupo::create([
                        'grupo' => $nuevogrupo['grupo'],
                        'cupo' => $nuevogrupo['cupo'],
                        'id_detalle' => $nuevodetallesolicitud->id,
                        'id_profesor' => $nuevogrupo['id_profesor'],
                        'id_horario' => $nuevohorario->id,
                    ]);
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
                        $grupos = SolicitudGrupo::where('id_detalle', $id_detalle)->get();
                        $buscardetalle = DetalleSolicitud::where('id', $id_detalle)->first();
                        $cursoeliminado = Curso::where('id', $buscardetalle->id_curso)->first();

                        foreach ($grupos as $grupo) {
                            // almacenamos el id horario antes de eliminar el grupo
                            $id_horario = $grupo->id_horario;
                            SolicitudGrupo::where('id', $grupo->id)->delete();
                            // una vez eliminado el grupo eliminamos los dias y el horario asignado al grupo
                            Dias::where('id_horario', '=', $id_horario)->delete(); // Elimina los dias del horario
                            Horario::where('id', $id_horario)->delete(); // Elimina el horario asociado al grupo
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
                        Dias::Where('id_horario', $grupoeliminar->id_horario)->delete();
                        Horario::Where('id', $grupoeliminar->id_horario)->delete();

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
            $nombrecurso = Curso::where('id', $idcurso->id_curso)->first();

            foreach ($grupo as $nuevogrupo) {
                // por orben de las relaciones primero hacemos el horario
                $nuevohorario = Horario::create([
                    'tipo' => 'SolicitudGrupo',
                ]);

                // Recorremos e insertamos los dias del nuevo grupo
                foreach ($nuevogrupo['horario'] as $dias) {
                    $nuevodia = Dias::create([
                        'id_dia' => $dias['id_dia'],
                        'entrada' => $dias['entrada'],
                        'salida' => $dias['salida'],
                        'id_horario' => $nuevohorario->id,
                    ]);
                }
                // ahora si ingresamos la referencia del grupo a la base de datos
                $nuevogrupo = SolicitudGrupo::create([
                    'grupo' => $nuevogrupo['grupo'],
                    'cupo' => $nuevogrupo['cupo'],
                    'id_detalle' => $id_detalle,
                    'id_profesor' => $nuevogrupo['id_profesor'],
                    'id_horario' => $nuevohorario->id,
                ]);
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
            $grupoeditar->id_profesor = $gruporequest['id_profesor'];
            // vamos a editar el horario eliminando los dias que hay y agregando los nuevos sin alterar el id del horario
            Dias::where('id_horario', $grupoeditar->id_horario)->delete();
            // recorremos los nuevos dias de su grupo
            foreach ($gruporequest['horario'] as $dias) {
                // añadimos los nuevos dia con su referencia apropiada
                $nuevodia = Dias::create([
                    'id_dia' => $dias['id_dia'],
                    'entrada' => $dias['entrada'],
                    'salida' => $dias['salida'],
                    'id_horario' => $grupoeditar->id_horario,
                ]);
            }
            $grupoeditar->save();

            return [' se actualizo el grupo ' => $grupoeditar->grupo];
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
