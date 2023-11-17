<?php

namespace App\Http\Controllers;

use App\Models\HorariosGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HorariosGrupoController extends Controller
{
    public function agregue(Request $request)
    {
        $validator =
        Validator::make($request->all(), [
            '*.dia_id' => 'required',
            '*.solicitud_grupo_id' => 'required',
            '*.hora_inicio' => 'required',
            '*.hora_fin' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        DB::beginTransaction();
        try {
            $horarios = collect($request->all())->map(function ($horario) {
                $horarioNuevo = HorariosGrupo::create($horario);

                return $horarioNuevo;
            });
            $horarios->first()->solicitudGrupo->detalleSolicitud->solicitudCurso->update(['estado_id' => 7]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 422);
        }

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
        $horariosGrupo = HorariosGrupo::where('solicitud_grupo_id', $request->id)->with(['dia'])->get();

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
        $horario = HorariosGrupo::find($request->id);
        $horario->solicitudGrupo->detalleSolicitud->solicitudCurso->update(['estado_id' => 7]);
        $horario->delete();

        return response()->json(['Message' => 'Horario eliminado'], 200);
    }
}
