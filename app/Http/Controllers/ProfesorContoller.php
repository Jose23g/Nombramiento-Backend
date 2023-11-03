<?php

namespace App\Http\Controllers;

use App\Models\Carga;
use App\Models\DetalleSolicitud;
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
        $provincia = $request->user()->persona->provincia->nombre;
        $canton = $request->user()->persona->canton->nombre;

        $persona = Persona::where("id", $profesor->persona_id)->first();
        $telefonos = Telefono::where("persona_id", $persona->id)->first();
        $cursoInfo = [];

        foreach ($carreras as $carrera) {
            $solicitudINFO[] = $this->obtenerUltimaSolicitudPorCarrera($carrera->id, 4);
        }

        foreach ($solicitudINFO as $solicitud) {
            foreach ($solicitud as $key) {
                // Accede a los campos de cada curso usando $key y $value
                $curso = Curso::find($key->curso_id);
                $carga = Carga::find($key->carga_id);
                if ($curso) {
                    $cursoInfo[] = (object) [
                        'Codigo' => $curso->sigla,
                        'nombre_del_curso' => $curso->nombre,
                        'carga' => $carga->nombre
                    ];
                }


            }
        }

        $borradorP6 = [
            "ID_Profesor" => $profesor->id,
            "Nombre" => $persona->nombre,
            "Cedula" => $persona->cedula,
            "Correo" => $profesor->correo,
            "OtroCorreo" => $profesor->otro_correo,
            "provincia" => $provincia,
            "canton" => $canton,
            "telefonos" => [
                "personal" => $telefonos->personal,
                "trabajo" => $telefonos->trabajo,
            ],
            "Cursos" => $cursoInfo,
            "Actividades" => 'actividades',
        ];

        return response()->json($borradorP6);
    }

    public function obtenerUltimaSolicitudPorCarrera($carreraID, $profesorID)
    {
        $solicitud = SolicitudCurso::where('carrera_id', $carreraID)
            ->where('id', 29)
            ->latest()
            ->first('id');

        $cursos = DetalleSolicitud::join('solicitud_grupos', 'detalle_solicitudes.id', '=', 'solicitud_grupos.detalle_solicitud_id')
            ->where('solicitud_grupos.profesor_id', 4)
            ->where('detalle_solicitudes.solicitud_curso_id', $solicitud->id)
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
