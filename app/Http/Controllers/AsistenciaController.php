<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\AsistenciaEstudiante;
use App\Models\Horario;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\Pase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    public function generatePdf(Asistencia $asistencia)
    {
        $asistencia->load([
            'materia',
            'profesor',
            'horario' => function ($query) {
                $query->select('id', 'aula');
            }
        ]);

        $estudiantes = AsistenciaEstudiante::join('estudiantes', 'asistencia_estudiante.estudiante_id', '=', 'estudiantes.id')
            ->where('asistencia_id', $asistencia->id)
            ->select(
                'asistencia_estudiante.id',
                'asistencia_estudiante.estudiante_id',
                'asistencia_estudiante.estado',
                'asistencia_estudiante.observacion_individual',
                'estudiantes.nombres',
                'estudiantes.apellidos'
            )
            ->get();

        $asistencia->estudiantes = $estudiantes;

        $pdf = Pdf::loadView('asistencias.registro-pdf', compact('asistencia'));
        return $pdf->stream('registro_asistencia_' . $asistencia->id . '_' . date('Y-m-d') . '.pdf');
    }

    public function edit(Asistencia $asistencia)
    {
        try {
            if ($asistencia->profesor_id !== auth()->user()->profesor->id) {
                return redirect()->back()->with('error', 'No tienes permiso para editar esta asistencia');
            }

            $asistencia->load([
                'materia',
                'profesor',
                'horario' => function ($query) {
                    $query->select('id', 'aula');
                }
            ]);

            $estudiantes = AsistenciaEstudiante::join('estudiantes', 'asistencia_estudiante.estudiante_id', '=', 'estudiantes.id')
                ->where('asistencia_id', $asistencia->id)
                ->select(
                    'asistencia_estudiante.id',
                    'asistencia_estudiante.estudiante_id',
                    'asistencia_estudiante.estado',
                    'asistencia_estudiante.observacion_individual',
                    'estudiantes.nombres',
                    'estudiantes.apellidos'
                )
                ->get();

            $asistencia->estudiantes = $estudiantes;

            $fecha = $asistencia->fecha->format('Y-m-d');

            $pasesActivos = Pase::where('horario_id', $asistencia->horario_id)
                ->where('fecha', $fecha)
                ->where('aprobado', true)
                ->with('estudiante')
                ->get();

            $estudiantesConPase = $pasesActivos->pluck('estudiante_id')->toArray();

            return view('asistencias.edit', [
                'asistencia' => $asistencia,
                'estudiantesConPase' => $estudiantesConPase,
                'tiene_observacion_profesor' => !empty($asistencia->profesor_observacion)
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar la información de la asistencia');
        }
    }

    public function update(Request $request, Asistencia $asistencia)
    {
        try {
            if ($asistencia->profesor_id !== auth()->user()->profesor->id) {
                return redirect()->back()->with('error', 'No tienes permiso para editar esta asistencia');
            }

            $checkboxes = ['falta_justificada', 'tarea_pendiente', 'conducta', 'pase_salida', 'retraso', 's_o'];
            foreach ($checkboxes as $checkbox) {
                $request[$checkbox] = $request->has($checkbox);
            }

            $validated = $request->validate([
                'fecha' => 'required|date',
                'hora_inicio' => 'required',
                'contenido_clase' => 'required',
                'observacion_general' => 'nullable|string',
                'profesor_observacion' => 'nullable|string',
                'falta_justificada' => 'nullable|boolean',
                'tarea_pendiente' => 'nullable|boolean',
                'conducta' => 'nullable|boolean',
                'pase_salida' => 'nullable|boolean',
                'retraso' => 'nullable|boolean',
                's_o' => 'nullable|boolean',
                'estudiantes' => 'required|array',
                'estudiantes.*.estado' => 'required|in:P,A,I',
                'estudiantes.*.observacion_individual' => 'nullable|string'
            ]);

            $asistencia->update([
                'fecha' => $validated['fecha'],
                'hora_inicio' => $validated['hora_inicio'],
                'contenido_clase' => $validated['contenido_clase'],
                'observacion_general' => $validated['observacion_general'],
                'profesor_observacion' => $validated['profesor_observacion'],
                'falta_justificada' => $validated['falta_justificada'] ?? false,
                'tarea_pendiente' => $validated['tarea_pendiente'] ?? false,
                'conducta' => $validated['conducta'] ?? false,
                'pase_salida' => $validated['pase_salida'] ?? false,
                'retraso' => $validated['retraso'] ?? false,
                's_o' => $validated['s_o'] ?? false
            ]);

            $asistencia->estudiantes()->sync($validated['estudiantes']);

            return redirect()->route('dashboard')
                ->with('success', 'Asistencia actualizada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al actualizar asistencia', [
                'asistencia_id' => $asistencia->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al actualizar la asistencia');
        }
    }

    private function obtenerHistorialDiario($estudiantes, string $fecha): array
    {
        $estudianteIds = collect($estudiantes)->pluck('id')->filter()->values();

        if ($estudianteIds->isEmpty()) {
            return [];
        }

        $registros = AsistenciaEstudiante::select(
            'asistencia_estudiante.estudiante_id',
            'asistencia_estudiante.estado',
            'asistencias.hora_inicio'
        )
            ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
            ->whereIn('asistencia_estudiante.estudiante_id', $estudianteIds)
            ->whereDate('asistencias.fecha', $fecha)
            ->orderBy('asistencias.hora_inicio')
            ->get();

        return $registros
            ->groupBy('estudiante_id')
            ->map(function ($items) {
                $estados = $items->pluck('estado')->toArray();
                $ultimo = $estados ? end($estados) : null;

                return [
                    'estados' => $estados,
                    'cadena' => implode(' - ', $estados),
                    'ultimo' => $ultimo,
                ];
            })
            ->toArray();
    }

    public function index($materiaId = null)
    {
        if ($materiaId) {
            $materia = Materia::findOrFail($materiaId);
            $profesor = auth()->user()->profesor;

            $pasesActivos = Pase::whereHas('horario', function ($query) use ($materia) {
                $query->where('materia_id', $materia->id);
            })
                ->whereDate('fecha', date('Y-m-d'))
                ->where('aprobado', true)
                ->with('estudiante')
                ->get();

            $estudiantes = DB::table('estudiantes')
                ->join('secciones', 'estudiantes.seccion_id', '=', 'secciones.id')
                ->join('asignaciones', 'secciones.id', '=', 'asignaciones.seccion_id')
                ->where('asignaciones.materia_id', $materia->id)
                ->select('estudiantes.*')
                ->get();

            $pasesActivosParaVista = [
                'pases' => $pasesActivos->map(function ($pase) {
                    return [
                        'id' => $pase->id,
                        'estudiante_id' => $pase->estudiante_id,
                        'fecha' => $pase->fecha,
                        'aprobado' => $pase->aprobado
                    ];
                })->toArray(),
                'total' => $pasesActivos->count()
            ];

            $fecha = now('America/Caracas')->format('Y-m-d');
            $historialAsistenciaDia = $this->obtenerHistorialDiario($estudiantes, $fecha);

            return view('asistencias.index', compact('materia', 'profesor', 'estudiantes', 'fecha'))
                ->with('historialAsistenciaDia', $historialAsistenciaDia)
                ->with('pasesActivosParaVista', $pasesActivosParaVista);
        }

        $asistencias = Asistencia::with([
            'profesor' => function ($query) {
                $query->with('user:id,name');
            },
            'materia' => function ($query) {
                $query->select('id', 'nombre');
            },
            'estudiantes' => function ($query) {
                $query->select('id', 'estudiante_id', 'estado', 'observacion_individual');
            }
        ])
            ->where('profesor_id', auth()->user()->profesor->id)
            ->orderBy('fecha', 'desc')
            ->get();

        return view('asistencias.lista', compact('asistencias'));
    }

    public function notasClase(Request $request)
    {
        $profesor = auth()->user()->profesor;
        if (!$profesor) {
            return redirect()->back()->with('error', 'No tienes un perfil de profesor asociado');
        }

        $asistencias = Asistencia::with(['materia', 'profesor', 'horario'])
            ->where('profesor_id', $profesor->id)
            ->whereNotNull('profesor_observacion')
            ->orderBy('fecha', 'desc')
            ->get();

        return view('asistencias.notas-clase', compact('asistencias'));
    }

    public function notasClasePdfIndividual(Asistencia $asistencia)
    {
        $profesor = auth()->user()->profesor;
        if (!$profesor || $asistencia->profesor_id != $profesor->id) {
            return redirect()->back()->with('error', 'No tienes permiso para ver esta nota');
        }

        $asistencia->load(['materia', 'profesor', 'horario']);

        $pdf = Pdf::loadView('asistencias.notas-clase-pdf-individual', compact('asistencia'));
        return $pdf->stream('nota_clase_' . $asistencia->id . '_' . date('Y-m-d') . '.pdf');
    }

    public function registrar($materiaId, $horarioId)
    {
        try {
            if (!auth()->check()) {
                return redirect()->route('login')->with('error', 'Por favor, inicie sesión primero');
            }

            $profesor = auth()->user()->profesor;
            if (!$profesor) {
                return redirect()->back()->with('error', 'No tienes un perfil de profesor asociado');
            }

            $materia = Materia::findOrFail($materiaId);
            $horario = Horario::where('id', $horarioId)
                ->whereHas('asignacion.materia', function ($query) use ($materia) {
                    $query->where('id', $materia->id);
                })
                ->with('asignacion')
                ->first();

            if (!$horario) {
                return redirect()->back()->with('error', 'Horario no encontrado o no válido');
            }

            $estudiantesIds = json_decode($horario->asignacion->estudiantes_id, true) ?? [];

            if (empty($estudiantesIds)) {
                return redirect()->back()->with('error', 'No hay estudiantes asignados a esta materia en la sección seleccionada');
            }

            $estudiantes = Estudiante::whereIn('id', $estudiantesIds)
                ->orderBy('apellidos')
                ->get();

            if ($estudiantes->isEmpty()) {
                return redirect()->back()->with('error', 'No se encontraron estudiantes con los IDs especificados en la asignación');
            }

            $fecha = now('America/Caracas')->format('Y-m-d');

            $pasesActivos = Pase::where('horario_id', $horario->id)
                ->where('fecha', $fecha)
                ->where('aprobado', true)
                ->with('estudiante')
                ->get();

            $estudiantesConPase = $pasesActivos->pluck('estudiante_id')->toArray();

            $pasesConMotivos = $pasesActivos->map(function ($pase) {
                return [
                    'estudiante_id' => $pase->estudiante_id,
                    'motivo' => $pase->motivo
                ];
            })->keyBy('estudiante_id')->toArray();

            $historialAsistenciaDia = $this->obtenerHistorialDiario($estudiantes, $fecha);

            return view('asistencias.index', [
                'materia' => $materia,
                'horario' => $horario,
                'estudiantes' => $estudiantes,
                'fecha' => $fecha,
                'estudiantesConPase' => $estudiantesConPase,
                'pasesConMotivos' => $pasesConMotivos,
                'historialAsistenciaDia' => $historialAsistenciaDia
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en AsistenciaController@registrar: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la información de la clase. Por favor, contacte al administrador.');
        }
    }

    public function create($materiaId, $horarioId)
    {
        try {
            if (!auth()->check()) {
                return redirect()->route('login')->with('error', 'Por favor, inicie sesión primero');
            }

            $profesor = auth()->user()->profesor;
            if (!$profesor) {
                return redirect()->back()->with('error', 'No tienes un perfil de profesor asociado');
            }

            $materia = Materia::findOrFail($materiaId);
            $horario = Horario::where('id', $horarioId)
                ->whereHas('asignacion.materia', function ($query) use ($materia) {
                    $query->where('id', $materia->id);
                })
                ->first();

            if (!$horario) {
                return redirect()->back()->with('error', 'Horario no válido para esta materia');
            }

            if (!$horario->asignacion) {
                return redirect()->back()->with('error', 'No se encontró asignación para este horario');
            }

            if (!$horario->asignacion->seccion) {
                return redirect()->back()->with('error', 'No se encontró la sección asociada');
            }

            $estudiantes = $horario->asignacion->seccion->estudiantes()->orderBy('apellidos')->get();

            if ($estudiantes->isEmpty()) {
                return redirect()->back()->with('error', 'No hay estudiantes asignados a esta sección');
            }

            $fecha = now('America/Caracas')->format('Y-m-d');

            $pasesActivos = Pase::where('horario_id', $horario->id)
                ->where('fecha', $fecha)
                ->where('aprobado', true)
                ->with('estudiante')
                ->get();

            $estudiantesConPase = $pasesActivos->pluck('estudiante_id')->toArray();
            $historialAsistenciaDia = $this->obtenerHistorialDiario($estudiantes, $fecha);

            return view('asistencias.index', [
                'materia' => $materia,
                'estudiantes' => $estudiantes,
                'horario' => $horario,
                'estudiantesConPase' => $estudiantesConPase,
                'fecha' => $fecha,
                'historialAsistenciaDia' => $historialAsistenciaDia
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar la información de la clase. Por favor, contacte al administrador.');
        }
    }

    public function store(Request $request, Materia $materia)
    {
        try {
            $checkboxes = ['aprobado', 's_o', 'falta_justificada', 'tarea_pendiente', 'conducta', 'pase_salida', 'retraso'];
            foreach ($checkboxes as $checkbox) {
                $request[$checkbox] = $request->has($checkbox);
            }

            $validated = $request->validate([
                'fecha' => 'required|date',
                'hora_inicio' => 'required|date_format:H:i',
                'horario_id' => 'required|exists:horarios,id',
                'contenido_clase' => 'required',
                'estudiantes' => 'required|array',
                'estudiantes.*.estado' => 'required|in:P,A,I',
                'estudiantes.*.observacion_individual' => 'nullable|string',
                'observacion_general' => 'nullable|string',
                'profesor_observacion' => 'nullable|string',
                'falta_justificada' => 'nullable|boolean',
                'tarea_pendiente' => 'nullable|boolean',
                'conducta' => 'nullable|boolean',
                'pase_salida' => 'nullable|boolean',
                'retraso' => 'nullable|boolean',
                's_o' => 'nullable|boolean',
            ]);

            $horario = Horario::findOrFail($validated['horario_id']);

            DB::transaction(function () use ($request, $materia, $validated) {
                $fechaActualCaracas = Carbon::now()->setTimezone('America/Caracas');
                $fechaActual = $fechaActualCaracas->format('Y-m-d H:i:s');

                $horario = Horario::with(['asignacion.materia', 'asignacion.seccion.grado'])
                    ->findOrFail($request->input('horario_id'));

                if (!$horario->asignacion || !$horario->asignacion->seccion || !$horario->asignacion->seccion->grado) {
                    return redirect()->back()->withErrors([
                        'horario_id' => 'El horario seleccionado no tiene una asignación válida'
                    ])->withInput();
                }

                $asignacion = $horario->asignacion;
                $materia = $asignacion->materia;
                $grado = $asignacion->seccion->grado;

                $asistencia = Asistencia::create([
                    'materia_id' => $materia->id,
                    'profesor_id' => auth()->user()->profesor->id,
                    'grado_id' => $grado->id,
                    'horario_id' => $horario->id,
                    'fecha' => $fechaActual,
                    'hora_inicio' => $validated['hora_inicio'],
                    'contenido_clase' => $validated['contenido_clase'],
                    'observacion_general' => $validated['observacion_general'],
                    'profesor_observacion' => $validated['profesor_observacion'],
                    'falta_justificada' => $validated['falta_justificada'] ?? false,
                    'tarea_pendiente' => $validated['tarea_pendiente'] ?? false,
                    'conducta' => $validated['conducta'] ?? false,
                    'pase_salida' => $validated['pase_salida'] ?? false,
                    'retraso' => $validated['retraso'] ?? false,
                    's_o' => $validated['s_o'] ?? false,
                    'created_at' => $fechaActual,
                    'updated_at' => $fechaActual
                ]);

                $pasesActivos = Pase::where('horario_id', $request->horario_id)
                    ->where('fecha', $request->fecha)
                    ->where('aprobado', true)
                    ->get()
                    ->pluck('estudiante_id');

                foreach ($request->estudiantes as $estudianteId => $data) {
                    $estado = $pasesActivos->contains($estudianteId) ? 'P' : $data['estado'];

                    AsistenciaEstudiante::create([
                        'asistencia_id' => $asistencia->id,
                        'estudiante_id' => $estudianteId,
                        'estado' => $estado,
                        'observacion_individual' => $data['observacion_individual'] ?? null
                    ]);
                }
            });

            return redirect()->route('dashboard')
                ->with('success', 'Asistencia registrada exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar la asistencia. Por favor, inténtelo nuevamente.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Asistencia $asistencia)
    {
        return view('asistencias.show', compact('asistencia'));
    }

    public function reporte(Request $request)
    {
        $dia_semana = $request->input('dia_semana');

        $asistencias = Asistencia::with([
            'profesor' => function ($query) {
                $query->with('user:id,name');
            },
            'materia' => function ($query) {
                $query->select('id', 'nombre');
            },
            'estudiantes' => function ($query) {
                $query->select('id', 'estudiante_id', 'estado', 'observacion_individual');
            }
        ])->when($dia_semana, function ($query) use ($dia_semana) {
            return $query->whereRaw("DAYOFWEEK(DATE(fecha)) = ?", [$dia_semana + 1]);
        })
            ->orderBy('fecha', 'desc')
            ->get();

        return view('asistencias.reporte', compact('asistencias'));
    }
}
