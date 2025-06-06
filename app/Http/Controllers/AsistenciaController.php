<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\AsistenciaEstudiante;
use App\Models\Horario;
use App\Models\Materia;
use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class AsistenciaController extends Controller
{
    public function generatePdf(Asistencia $asistencia)
    {
        $asistencia->load([
            'materia',
            'profesor',
            'horario' => function($query) {
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
                'horario' => function($query) {
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
            
            $asistencia->estudiantes = $estudiantes;;

            return view('asistencias.edit', compact('asistencia'));

        } catch (\Exception $e) {
            Log::error('Error al editar asistencia', [
                'asistencia_id' => $asistencia->id,
                'error' => $e->getMessage()
            ]);
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
                'falta_justificada' => $validated['falta_justificada'] ?? false,
                'tarea_pendiente' => $validated['tarea_pendiente'] ?? false,
                'conducta' => $validated['conducta'] ?? false,
                'pase_salida' => $validated['pase_salida'] ?? false,
                'retraso' => $validated['retraso'] ?? false,
                's_o' => $validated['s_o'] ?? false
            ]);

            $asistencia->estudiantes()->sync($validated['estudiantes']);

            return redirect()->route('asistencias.reporte')
                ->with('success', 'Asistencia actualizada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error al actualizar asistencia', [
                'asistencia_id' => $asistencia->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al actualizar la asistencia');
        }
    }

    public function index($materiaId = null)
    {
        if ($materiaId) {
            $materia = Materia::findOrFail($materiaId);
            $profesor = auth()->user()->profesor;

            $estudiantes = DB::table('estudiantes')
                ->join('secciones', 'estudiantes.seccion_id', '=', 'secciones.id')
                ->join('asignaciones', 'secciones.id', '=', 'asignaciones.seccion_id')
                ->where('asignaciones.materia_id', $materia->id)
                ->select('estudiantes.*')
                ->get();

            return view('asistencias.index', compact('materia', 'profesor', 'estudiantes'));
        }

        $asistencias = Asistencia::with([
            'profesor' => function($query) {
                $query->with('user:id,name');
            },
            'materia' => function($query) {
                $query->select('id', 'nombre');
            },
            'estudiantes' => function($query) {
                $query->select('id', 'estudiante_id', 'estado', 'observacion_individual');
            }
        ])
        ->where('profesor_id', auth()->user()->profesor->id)
        ->orderBy('fecha', 'desc')
        ->get();

        return view('asistencias.lista', compact('asistencias'));
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
                ->whereHas('asignacion.materia', function($query) use ($materia) {
                    $query->where('id', $materia->id);
                })
                ->first();

            if (!$horario) {
                return redirect()->back()->with('error', 'Horario no encontrado o no válido');
            }

            $estudiantes = $horario->asignacion->seccion->estudiantes()->orderBy('apellidos')->get();

            if ($estudiantes->isEmpty()) {
                return redirect()->back()->with('error', 'No hay estudiantes asignados a esta sección');
            }

            return view('asistencias.index', [
                'materia' => $materia,
                'horario' => $horario,
                'estudiantes' => $estudiantes
            ]);

        } catch (\Exception $e) {
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
                ->whereHas('asignacion.materia', function($query) use ($materia) {
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

            return view('asistencias.index', [
                'materia' => $materia,
                'estudiantes' => $estudiantes,
                'horario' => $horario
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar la información de la clase. Por favor, contacte al administrador.');
        }
    }

    public function store(Request $request, Materia $materia)
    {
        try {
            Log::info('Inicio de registro de asistencia', [
                'materia_id' => $materia->id,
                'request_data' => $request->all()
            ]);

            $checkboxes = ['aprobado', 's_o', 'falta_justificada', 'tarea_pendiente', 'conducta', 'pase_salida', 'retraso'];
            foreach ($checkboxes as $checkbox) {
                $request[$checkbox] = $request->has($checkbox);
            }
            
            Log::info('Validando datos de asistencia');
            $validated = $request->validate([
                'fecha' => 'required|date',
                'hora_inicio' => 'required',
                'horario_id' => 'required|exists:horarios,id',
                'contenido_clase' => 'required',
                'estudiantes' => 'required|array',
                'estudiantes.*.estado' => 'required|in:P,A,I',
                'estudiantes.*.observacion_individual' => 'nullable|string',
                'observacion_general' => 'nullable|string',
                'falta_justificada' => 'nullable|boolean',
                'tarea_pendiente' => 'nullable|boolean',
                'conducta' => 'nullable|boolean',
                'pase_salida' => 'nullable|boolean',
                'retraso' => 'nullable|boolean',
                's_o' => 'nullable|boolean'
            ]);

            Log::info('Datos validados correctamente', [
                'validated_data' => $validated
            ]);

            DB::transaction(function () use ($request, $materia, $validated) {
                Log::info('Buscando horario', [
                    'horario_id' => $request->input('horario_id')
                ]);

                $horario = Horario::with(['asignacion.materia', 'asignacion.seccion.grado'])
                    ->findOrFail($request->input('horario_id'));

                Log::info('Horario encontrado', [
                    'horario_data' => [
                        'id' => $horario->id,
                        'asignacion_id' => $horario->asignacion_id,
                        'seccion_id' => $horario->asignacion->seccion_id,
                        'grado_id' => $horario->asignacion->seccion->grado_id
                    ]
                ]);

                if (!$horario->asignacion || !$horario->asignacion->seccion || !$horario->asignacion->seccion->grado) {
                    Log::error('Horario sin asignación válida');
                    return redirect()->back()->withErrors([
                        'horario_id' => 'El horario seleccionado no tiene una asignación válida'
                    ])->withInput();
                }

                $asignacion = $horario->asignacion;
                $materia = $asignacion->materia;
                $grado = $asignacion->seccion->grado;

                Log::info('Creando asistencia', [
                    'materia_id' => $materia->id,
                    'profesor_id' => auth()->user()->profesor->id,
                    'grado_id' => $grado->id,
                    'horario_id' => $horario->id
                ]);

                $asistencia = Asistencia::create([
                    'materia_id' => $materia->id,
                    'profesor_id' => auth()->user()->profesor->id,
                    'grado_id' => $grado->id,
                    'horario_id' => $horario->id,
                    'fecha' => $validated['fecha'],
                    'hora_inicio' => $validated['hora_inicio'],
                    'hora_registro' => now(),
                    'contenido_clase' => $validated['contenido_clase'],
                    'observacion_general' => $validated['observacion_general'],
                    'falta_justificada' => $validated['falta_justificada'] ?? false,
                    'tarea_pendiente' => $validated['tarea_pendiente'] ?? false,
                    'conducta' => $validated['conducta'] ?? false,
                    'pase_salida' => $validated['pase_salida'] ?? false,
                    'retraso' => $validated['retraso'] ?? false,
                    's_o' => $validated['s_o'] ?? false
                ]);

                Log::info('Asistencia creada', [
                    'asistencia_id' => $asistencia->id
                ]);

                Log::info('Sincronizando estudiantes', [
                    'estudiantes_count' => count($validated['estudiantes'])
                ]);

                $estudiantesData = [];
                foreach ($validated['estudiantes'] as $estudianteId => $data) {
                    $estudiantesData[$estudianteId] = [
                        'estado' => $data['estado'],
                        'observacion_individual' => $data['observacion_individual'] ?? null
                    ];
                }

                $asistencia->estudiantes()->attach($estudiantesData);

                Log::info('Proceso de registro completado exitosamente');
            });

            return redirect()->route('horario.profesor')
                ->with('success', 'Asistencia registrada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al registrar asistencia', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
}
