<?php

namespace App\Http\Controllers;

use App\Models\Archivos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArchivosController extends Controller
{
    public function obtenga(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $archivo = Archivos::with(['propietario.persona', 'estado', 'estadoGeneral'])->find($request->id);
        return response()->json($archivo);
    }

    public function apruebe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'archivo' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $usuario = $request->user();
        $estado_id = 0;
        $responsable = [];
        switch ($usuario->rol_id) {
            case 2:
                $estado_id = 8;
                $responsable = ['usuario_coordinador_id' => $usuario->id];
                break;
            case 3:
                $estado_id = 10;
                $responsable = ['usuario_direccion_id' => $usuario->id];
                break;
            case 4:
                $estado_id = 12;
                $responsable = ['usuario_docencia_id' => $usuario->id, 'estado_general_id' => 1];
                break;
            default:
                return response()->json(['message' => 'Usuario no autorizado'], 400);
                break;
        }
        $archivo = Archivos::find($request->id);
        $archivo->update(['estado_id' => $estado_id, 'archivo' => $request->archivo, ...$responsable]);
        return response()->json(['message' => 'Se ha aprobado el archivo'], 200);
    }

    public function rechace(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'observacion' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $usuario = $request->user();
        $estado_id = 0;
        $responsable = [];
        switch ($usuario->rol_id) {
            case 2:
                $estado_id = 9;
                $responsable = ['usuario_coordinador_id' => $usuario->id];
                break;
            case 3:
                $estado_id = 11;
                $responsable = ['usuario_direccion_id' => $usuario->id];
                break;
            case 4:
                $estado_id = 13;
                $responsable = ['usuario_docencia_id' => $usuario->id];
                break;
            default:
                return response()->json(['message' => 'Usuario no autorizado', 400]);
                break;
        }
        $archivo = Archivos::find($request->id);
        $archivo->update(['estado_id' => $estado_id, 'estado_general_id' => 3, 'observacion' => $request->observacion, ...$responsable]);
    }

    public function guarde(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'archivo' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $usuario = $request->user();
        $estado_id = 0;
        switch ($usuario->rol_id) {
            case 1:
                $estado_id = 2;
                break;
            case 2:
                $estado_id = 8;
                break;
            default:
                return response()->json(['message' => 'Usuario no autorizado', 400]);
                break;
        }
        Archivos::create([
            'archivo' => $request->archivo,
            'usuario_propietario_id' => $request->user()->id,
            'estado_id' => $estado_id,
            'estado_general_id' => 2,
        ]);
        return response()->json(['message' => 'Guardado exitosamente'], 200);
    }
    public function obtengaElListadoParaElProfesor(Request $request)
    {
        return response()->json(Archivos::with(['estadoGeneral'])->where('usuario_propietario_id', $request->user()->id)->get());
    }
    public function obtengaElListado(Request $request)
    {
        $usuario = $request->user();
        $estado_id = 0;
        $rol_id = $usuario->rol_id;
        switch ($rol_id) {
            case 2:
                $estado_id = 2;
                break;
            case 3:
                $estado_id = 8;
                break;
            case 4:
                $estado_id = 10;
                break;
            default:
                return response()->json(['message' => 'Usuario no autorizado', 400]);
                break;
        }
        $archivos = Archivos::with(['propietario.persona', 'estado', 'estadoGeneral'])->where('estado_id', $estado_id)->get();

        if ($estado_id != 10) {
            $carreraDeUsuario = $usuario->usuarioCarreras->filter(function ($carrera) use (&$rol_id) {
                return $carrera->rol_id == $rol_id;
            })->first();
            $listadoDeArchivos = $archivos->filter(function ($archivo) use (&$carreraDeUsuario) {
                $carrera_id = $carreraDeUsuario->carrera_id;
                $carreras = $archivo->propietario->usuarioCarreras;
                return $carreras->contains(function ($carrera) use (&$carrera_id) {
                    return $carrera->carrera_id == $carrera_id;
                });
            });
            return response()->json($listadoDeArchivos);
        }
        return response()->json($archivos);
    }
}
