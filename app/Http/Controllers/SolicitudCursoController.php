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
        $solicitudCurso = SolicitudCurso::with(['coordinador.persona', 'estado', 'carrera', 'detallesSolicitud.solicitudGrupo.profesor', 'detallesSolicitud.solicitudGrupo.horario', 'detallesSolicitud.curso'])->find($validatedData['id']);

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
        $detallesSolicitud = $solicitudCurso->detallesSolicitud->map(function ($detalle) {
            $curso = collect([
                'id' => $detalle->curso->id,
                'sigla' => $detalle->curso->sigla,
                'nombre' => $detalle->curso->nombre,
                'creditos' => $detalle->curso->creditos]);
            $solicitudGrupo = $detalle->solicitudGrupo->map(function ($solicitud) {
                $profesor = collect([
                    'id' => $solicitud->profesor->id,
                    'correo' => $solicitud->profesor->correo,
                ]);
                $horario = collect([
                    'id' => $solicitud->horario->id,
                    'tipo' => $solicitud->horario->tipo,
                ]);
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
        'anio' => $solicitudCurso->anio,
        'semestre' => $solicitudCurso->semestre,
        'fecha' => $solicitudCurso->fecha,
        'observacion' => $solicitudCurso->observacion,
        'coordinador' => $coordinador,
        'estado' => $estado,
        'carrera' => $carrera,
        'detalles_solicitud' => $detallesSolicitud]);

        return $solicitudCursoRefactorized;
    }
}
