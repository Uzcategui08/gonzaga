<?php

namespace App\Http\Controllers\Extracurricular;

use App\Http\Controllers\Controller;
use App\Models\AsistenciaExtracurricular;
use App\Models\ClaseExtracurricular;
use App\Models\Profesor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AsistenciaExtracurricularController extends Controller
{
    public function form(ClaseExtracurricular $clase, Request $request)
    {
        $usuario = Auth::user();
        $esProfesorExtracurricular = $usuario instanceof User
            ? $usuario->hasRole('profesor_extracurricular')
            : false;

        $ahora = Carbon::now('America/Caracas');
        $fechaActual = $ahora->toDateString();

        $fechaSolicitada = $request->query('fecha') ?: $fechaActual;
        $fechaSolicitada = Carbon::parse($fechaSolicitada)->toDateString();

        if ($esProfesorExtracurricular) {
            $profesorId = Profesor::where('user_id', $usuario->id)->value('id');
            if (!empty($profesorId) && (int) $clase->profesor_id !== (int) $profesorId) {
                abort(403, 'No tiene permiso para esta clase.');
            }

            // Solo permitir fecha distinta a hoy si ya existe una asistencia (modo edición)
            if ($fechaSolicitada !== $fechaActual) {
                $existe = AsistenciaExtracurricular::where('clase_extracurricular_id', $clase->id)
                    ->whereDate('fecha', $fechaSolicitada)
                    ->exists();

                if (!$existe) {
                    abort(403, 'Solo puede editar asistencias ya registradas.');
                }
            }
        }

        // Validar día de semana vs la fecha solicitada (para todos los roles cuando exista el campo)
        if (Schema::hasColumn('clases_extracurriculares', 'dia_semana') && !empty($clase->dia_semana)) {
            $isoDeFecha = Carbon::parse($fechaSolicitada, 'America/Caracas')->isoWeekday();
            if ((int) $clase->dia_semana !== (int) $isoDeFecha) {
                abort(403, 'La fecha seleccionada no corresponde al día de esta clase.');
            }
        }

        $fecha = $esProfesorExtracurricular ? $fechaSolicitada : $fechaSolicitada;

        $asistencia = AsistenciaExtracurricular::where('clase_extracurricular_id', $clase->id)
            ->whereDate('fecha', $fecha)
            ->first();

        $clase->load(['estudiantes.seccion.grado']);

        $pivotPorEstudiante = collect();
        if ($asistencia) {
            $asistencia->load('estudiantes');
            $pivotPorEstudiante = $asistencia->estudiantes->keyBy('id');
        }

        return view('extracurricular.asistencia.form', [
            'clase' => $clase,
            'fecha' => $fecha,
            'fechaActual' => $fechaActual,
            'asistencia' => $asistencia,
            'pivotPorEstudiante' => $pivotPorEstudiante,
            'soloHoy' => $esProfesorExtracurricular,
        ]);
    }

    public function store(ClaseExtracurricular $clase, Request $request)
    {
        $usuario = Auth::user();
        $esProfesorExtracurricular = $usuario instanceof User
            ? $usuario->hasRole('profesor_extracurricular')
            : false;

        $validated = $request->validate([
            'fecha' => 'required|date',
            'hora_inicio' => 'nullable',
            'contenido_clase' => 'required|string',
            'observacion_general' => 'nullable|string',
            'estudiantes' => 'required|array',
            'estudiantes.*.estado' => 'required|in:A,I,P',
            'estudiantes.*.observacion_individual' => 'nullable|string',
        ]);

        if ($esProfesorExtracurricular) {
            $ahora = Carbon::now('America/Caracas');
            $hoy = $ahora->toDateString();

            $fechaEnviada = Carbon::parse($validated['fecha'])->toDateString();

            if (Schema::hasColumn('clases_extracurriculares', 'dia_semana') && !empty($clase->dia_semana)) {
                $isoDeFecha = Carbon::parse($fechaEnviada, 'America/Caracas')->isoWeekday();
                if ((int) $clase->dia_semana !== (int) $isoDeFecha) {
                    abort(403, 'La fecha seleccionada no corresponde al día de esta clase.');
                }
            }

            $profesorId = Profesor::where('user_id', $usuario->id)->value('id');
            if (!empty($profesorId) && (int) $clase->profesor_id !== (int) $profesorId) {
                abort(403, 'No tiene permiso para esta clase.');
            }

            if ($fechaEnviada !== $hoy) {
                $existe = AsistenciaExtracurricular::where('clase_extracurricular_id', $clase->id)
                    ->whereDate('fecha', $fechaEnviada)
                    ->exists();

                if (!$existe) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['fecha' => 'Solo puede editar asistencias ya registradas (no crear nuevas en otra fecha).']);
                }
            }
        }

        $fecha = Carbon::parse($validated['fecha'])->toDateString();

        $asistencia = AsistenciaExtracurricular::firstOrNew([
            'clase_extracurricular_id' => $clase->id,
            'fecha' => $fecha,
        ]);

        if (!$asistencia->exists) {
            $asistencia->created_by = Auth::id();
        }

        $asistencia->hora_inicio = $validated['hora_inicio'] ?? null;
        $asistencia->contenido_clase = $validated['contenido_clase'];
        $asistencia->observacion_general = $validated['observacion_general'] ?? null;

        // Compatibilidad con esquemas existentes: algunas BD tienen asistencias_extracurriculares.profesor_id NOT NULL
        if (Schema::hasColumn('asistencias_extracurriculares', 'profesor_id')) {
            $profesorId = $clase->profesor_id;
            if (empty($profesorId) && ($usuario instanceof User)) {
                $profesorId = Profesor::where('user_id', $usuario->id)->value('id');
            }

            $asistencia->profesor_id = $profesorId;
        }

        $asistencia->save();

        $clase->load('estudiantes:id');
        $permitidos = $clase->estudiantes->pluck('id')->map(fn($id) => (string) $id)->flip();

        $syncData = [];
        foreach (($validated['estudiantes'] ?? []) as $estudianteId => $detalle) {
            if (!isset($permitidos[(string) $estudianteId])) {
                continue;
            }
            $syncData[(int) $estudianteId] = [
                'estado' => $detalle['estado'],
                'observacion_individual' => $detalle['observacion_individual'] ?? null,
            ];
        }

        if (empty($syncData)) {
            return redirect()->back()->with('error', 'Debe registrar al menos un estudiante');
        }

        $asistencia->estudiantes()->sync($syncData);

        return redirect()->route('extracurricular.index')->with('success', 'Asistencia registrada');
    }
}
