<?php

namespace App\Http\Controllers;

use App\Models\Carga;
use App\Models\Persona;
use App\Models\Curso;
use App\Models\PSeis;
use App\Models\SolicitudCurso;
use App\Models\Telefono;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ProfesorContoller extends Controller
{
    public function generarP6(Request $request)
    {
        $profesorID = $request->user()->id;
        $validaciones = Validator::make([
            "cargo_categoria" => "required",
            "vig_desde" => "required",
            "vig_hasta" => "required",
            "jornada" => "required",
        ], [
            "cargo_categoria.required" => "Es necesario ingresar el cargo o categoria al que está asignado",
            "vig_desde.required" => "No se puede generar el borrador sin haber establecido el inicio de la vigencia",
            "vig_hasta.required" => "No se puede generar el borrador sin haber establecido el final de la vigencia",
            "jornada.required" => "Es necesario ingresar la jornada.",
        ]);

        if ($validaciones->fails()) {
            return response()->json(['errormessage' => $validaciones->errors()], 422);
        }
        $p6 = PSeis::create([
            "profesor_id" => $profesorID,
            "cargo_categoria" => $request->cargo_categoria,
            "vig_desde" => $request->vig_desde,
            "vig_hasta" => $request->vig_hasta,
            "jornada" => $request->jornada,
        ]);
    }

    public function previsualizarP6(Request $request)
    {
        $profesor = $request->user();
        $carreras = $request->user()->carreras;
        $persona = Persona::where("id", $profesor->persona_id)->first();
        $telefonos = Telefono::where("persona_id", $persona->id)->first();
        $ultimaSolicitud = [];
        $cursoInfo = [];
        foreach ($carreras as $carrera) {
            $ultimaSolicitud[] = $this->obtenerUltimaSolicitudPorCarrera($carrera->id, 4);
        }
        return response()->json($ultimaSolicitud);

        foreach ($ultimaSolicitud as $solictud) {
            if ($solictud) { // Verifica si $solictud no es null
                foreach ($solictud->detalleSolicitudes as $detalle_solicitud) {
                    if ($detalle_solicitud->solicitudGrupos) {
                        foreach ($detalle_solicitud->solicitudGrupos as $grupos) {
                            $cursoInfo[] = $this->obtenerInfoCurso($detalle_solicitud->curso_id, $grupos->carga_id);
                        }
                    }
                }
            }
        }



        /*  $borradorP6 = [
             "ID_Profesor" => $profesor->id,
             "Nombre" => $persona->nombre,
             "Cedula" => $persona->cedula,
             "Correo" => $profesor->correo,
             "OtroCorreo" => $profesor->otro_correo,
             "telefonos" => (object) [
                 $telefonos->personal,
                 $telefonos->trabajo,
             ],
             "Cursos" => (object) $cursoIds,
         ];

         return response()->json($borradorP6); */
    }

    public function obtenerUltimaSolicitudPorCarrera($carreraID, $profesorID)
    {
        /*  $solicitud = SolicitudCurso::where('carrera_id', $carreraID)->where('id', 29)->latest()->with('detalleSolicitudes.solicitudGrupos')->first(); */
        $solicitud = SolicitudCurso::where('carrera_id', $carreraID)->where('id', 29)
            ->with([
                'detalleSolicitudes' => function ($query) {
                    $query->select('id', 'curso_id', 'solicitud_curso_id');
                },
                'detalleSolicitudes.solicitudGrupos' => function ($query) {
                    $query->select('detalle_solicitud_id', 'profesor_id', 'carga_id');
                    $query->where('profesor_id', 4);
                }
            ])
            ->latest()
            ->first(['id', 'carrera_id']);

        return $solicitud;
    }

    public function obtenerInfoCurso($cursoID, $cargaID)
    {
        $curso = Curso::find($cursoID)->select('nombre', 'sigla');
        $carga = Carga::find($cargaID)->select('nombre');

        $curso->carga = $carga;
        return $curso;
    }
}
