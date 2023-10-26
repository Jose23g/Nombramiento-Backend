<?php

namespace App\Http\Controllers;

use App\Models\SolicitudCurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SolicitudCursoController extends Controller
{
    public function muestreUnaSolicitud(Request $request)
    {
        $validator =
            Validator::make($request->all(), ['id' => 'required'], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();
        $solicitudCurso = SolicitudCurso::with(['coordinador.persona', 'estado', 'fechaSolicitud', 'carrera', 'detalleSolicitudes.solicitudGrupos.profesor', 'detalleSolicitudes.solicitudGrupos.horarioGrupos.dia', 'detalleSolicitudes.curso'])->find($validatedData['id']);
        if ($solicitudCurso) {
            $persona = collect([
                'id' => $solicitudCurso->coordinador->persona->id,
                'nombre' => $solicitudCurso->coordinador->persona->nombre,
            ]);
            $coordinador = collect([
            'id' => $solicitudCurso->coordinador->id,
            'persona' => $persona,
            ]);
            $estado = collect([
                'id' => $solicitudCurso->estado->id,
                'nombre' => $solicitudCurso->estado->nombre,
            ]);
            $carrera = collect([
                'id' => $solicitudCurso->carrera->id,
                'nombre' => $solicitudCurso->carrera->nombre,
            ]);
            $fechaSolicitud = collect([
                'id' => $solicitudCurso->fechaSolicitud->id,
                'anio' => $solicitudCurso->fechaSolicitud->anio,
                'ciclo' => $solicitudCurso->fechaSolicitud->ciclo,
                'fecha_inicio' => $solicitudCurso->fechaSolicitud->fecha_inicio,
                'fecha_fin' => $solicitudCurso->fechaSolicitud->fecha_fin,
            ]);
            $detallesSolicitud = $solicitudCurso->detalleSolicitudes->map(function ($detalle) {
                $curso = collect([
                    'id' => $detalle->curso->id,
                    'sigla' => $detalle->curso->sigla,
                    'nombre' => $detalle->curso->nombre,
                    'creditos' => $detalle->curso->creditos]);
                $solicitudGrupo = $detalle->solicitudGrupos->map(function ($solicitud) {
                    $profesor = collect([
                        'id' => $solicitud->profesor->id,
                        'correo' => $solicitud->profesor->correo,
                    ]);
                    $horario = $solicitud->horarioGrupos->map(function ($horario) {
                        $horarioRefactorized = collect([
                            'id' => $horario->id,
                            'dia' => $horario->dia->nombre,
                            'hora_inicio' => $horario->hora_inicio,
                            'hora_fin' => $horario->hora_fin,
                        ]);

                        return $horarioRefactorized;
                    });
                    $solicitudRefactorized = collect([
                        'id' => $solicitud->id,
                        'grupo' => $solicitud->grupo,
                        'cupo' => $solicitud->cupo,
                        'id_detalle' => $solicitud->id_detalle,
                        'id_profesor' => $solicitud->id_profesor,
                        'id_horario' => $solicitud->id_horario,
                        'profesor' => $profesor,
                        'horario' => $horario,
                        ]);

                    return $solicitudRefactorized;
                });
                $detalleRefactorized = collect([
                    'id' => $detalle->id,
                    'ciclo' => $detalle->ciclo,
                    'grupos' => $detalle->grupos,
                    'recinto' => $detalle->recinto,
                    'carga' => $detalle->carga,
                    'id_solicitud' => $detalle->id_solicitud,
                    'id_curso' => $detalle->id_curso,
                    'curso' => $curso,
                    'solicitud_grupo' => $solicitudGrupo,
                ]);

                return $detalleRefactorized;
            });
            $solicitudCursoRefactorized = collect([
            'id' => $solicitudCurso->id,
            'carga_total' => $solicitudCurso->carga_total,
            'observacion' => $solicitudCurso->observacion,
            'coordinador' => $coordinador,
            'fecha_solicitud' => $fechaSolicitud,
            'estado' => $estado,
            'carrera' => $carrera,
            'detalle_solicitudes' => $detallesSolicitud]);

            return $solicitudCursoRefactorized;
        }

        return $solicitudCurso;
    }
}
