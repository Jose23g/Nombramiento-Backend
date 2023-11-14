<?php

namespace App\Http\Controllers;

use App\Models\HorariosTrabajo;

class HorariosTrabajoController extends Controller
{
    public function agregue($request)
    {
        $horarios = collect($request['horarios'])->map(function ($horario) use (&$request) {
            $horarioNuevo = HorariosTrabajo::create([
                'trabajo_id' => $request['trabajo_id'],
                'dia_id' => $horario['dia_id'],
                'hora_inicio' => $horario['hora_inicio'],
                'hora_fin' => $horario['hora_fin'],
            ]);

            return $horarioNuevo;
        });

        return response()->json(['Message' => 'Se ha registrado con éxito'], 200);
    }

    public function obtengaLaLista(Request $request)
    {
        $validator =
        Validator::make($request->all(), ['id' => 'required'], [
            'required' => 'El campo :attribute es requerido.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $horariosGrupo = HorariosTrabajo::where('solicitud_grupo_id', $request->id)->with(['dia'])->get();

        return response()->json($horariosGrupo, 200);
    }

    public function elimineElHorario(Request $request)
    {
        $validator =
        Validator::make($request->all(), [
            'id' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $horario = HorariosTrabajo::find($request->id);
        $horario->solicitudGrupo->detalleSolicitud->solicitudCurso->update(['estado_id' => 7]);
        $horario->delete();

        return response()->json(['Message' => 'Horario eliminado'], 200);
    }
}
