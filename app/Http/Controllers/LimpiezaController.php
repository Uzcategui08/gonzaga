<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Limpieza;
use App\Models\Horario;
use App\Models\Aula;
use App\Models\Estudiante;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LimpiezaController extends Controller
{
    public function index()
    {
        $fechaActual = Carbon::now('America/Caracas');
        $diaIngles = $fechaActual->format('l');

        $diasTraduccion = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];

        $diaActual = $diasTraduccion[$diaIngles] ?? 'Lunes';

        $clasesHoy = Horario::where('dia', $diaActual)
            ->with(['asignacion.materia', 'asignacion.seccion', 'asignacion.seccion.grado', 'asignacion.profesor'])
            ->get();

        $esCoordinador = auth()->user()->hasRole('coordinador');
        $esProfesor = auth()->user()->hasRole('profesor');

        $clasesHoy = Horario::where('dia', $diaActual)
            ->with(['asignacion.materia', 'asignacion.seccion', 'asignacion.seccion.grado', 'asignacion.profesor'])
            ->get();

        if ($esProfesor) {
            $clasesHoy = $clasesHoy->filter(function($clase) {
                return $clase->asignacion && $clase->asignacion->profesor_id === auth()->id();
            });
        }

        if ($esCoordinador) {
            $limpiezas = Limpieza::whereDate('fecha', $fechaActual)
                ->with(['profesor.usuario'])
                ->orderBy('fecha', 'desc')
                ->get();
        } else {
            $limpiezas = Limpieza::where('profesor_id', auth()->id())
                ->with(['profesor.usuario'])
                ->orderBy('fecha', 'desc')
                ->get();
        }

        return view('limpiezas.index', compact('clasesHoy', 'limpiezas', 'esCoordinador', 'esProfesor'));
    }

    public function create(Request $request, $id = null)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Por favor, inicie sesión primero');
        }

        try {
            $esCoordinador = auth()->user()->hasRole('coordinador');
            $esProfesor = auth()->user()->hasRole('profesor');

            if ($esProfesor) {
                $profesor = auth()->user()->profesor;
                if (!$profesor) {
                    return redirect()->back()->with('error', 'No tienes un perfil de profesor asociado');
                }

                $diaActual = Carbon::now('America/Caracas')->format('l');
                $clasesProfesor = Horario::whereHas('asignacion', function($query) use ($profesor) {
                    $query->where('profesor_id', $profesor->id);
                })
                ->where('dia', $diaActual)
                ->with([
                    'asignacion.profesor',
                    'asignacion.usuario',
                    'asignacion.seccion.estudiantes',
                    'asignacion.materia'
                ])
                ->get();
            } else if ($esCoordinador) {
                $clasesProfesor = collect();
            } else {
                return redirect()->back()->with('error', 'No tienes permisos para acceder a esta sección');
            }

            $horarioId = $id ?? $request->query('clase');

            $horarioSeleccionado = null;
            $estudiantes = collect();
            $hora_inicio = null;
            $hora_fin = null;

            if ($horarioId) {
                $horarioSeleccionado = Horario::with([
                    'asignacion.profesor',
                    'asignacion.usuario',
                    'asignacion.seccion.estudiantes',
                    'asignacion.materia'
                ])
                    ->where('id', $horarioId)
                    ->first();

                if (!$horarioSeleccionado || !$horarioSeleccionado->asignacion || !$horarioSeleccionado->asignacion->profesor_id) {
                    return redirect()->back()->with('error', 'La clase seleccionada no tiene un profesor asignado');
                }

                if ($horarioSeleccionado) {
                    $hora_inicio = Carbon::parse($horarioSeleccionado->hora_inicio)->format('H:i');
                    $hora_fin = Carbon::parse($horarioSeleccionado->hora_fin)->format('H:i');

                    $estudiantesDeSeccion = $horarioSeleccionado->asignacion->seccion->estudiantes()
                        ->orderBy('apellidos')
                        ->get();

                    $estudiantes = collect();
                    if ($request->old('estudiantes')) {
                        $estudiantes = collect($request->old('estudiantes'))
                            ->map(function($estudianteData) use ($estudiantesDeSeccion) {
                                return $estudiantesDeSeccion->firstWhere('id', $estudianteData['id']);
                            })
                            ->filter(); 
                    }

                    $estudiantesDisponibles = $estudiantesDeSeccion->filter(function($estudiante) use ($estudiantes) {
                        return !$estudiantes->contains('id', $estudiante->id);
                    });

                    if ($estudiantes->isEmpty() && !$estudiantesDisponibles->isEmpty()) {
                        $estudiantes = collect(); 
                    } else if ($estudiantes->isEmpty() && $estudiantesDisponibles->isEmpty()) {
                        return redirect()->back()->with('error', 'No hay estudiantes asignados a esta sección');
                    }

                    return view('limpiezas.create', [
                        'ultimasClases' => $clasesProfesor->sortByDesc('hora_fin'),
                        'horarioSeleccionado' => $horarioSeleccionado,
                        'estudiantes' => $estudiantes,
                        'estudiantesDisponibles' => $estudiantesDisponibles,
                        'hora_inicio' => $hora_inicio,
                        'hora_fin' => $hora_fin
                    ]);
                } else {
                    return redirect()->back()->with('error', 'Horario no encontrado o no válido');
                }
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el formulario. Por favor, contacte al administrador.');
        }
    }
    

    public function getEstudiantes(Horario $clase)
    {
        $estudiantes = $clase->asignacion->seccion->estudiantes;
        return response()->json($estudiantes);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'profesor_id' => 'required|exists:users,id',
                'horario_id' => 'required|exists:horarios,id',
                'fecha' => 'required|date',
                'hora_inicio' => 'required',
                'hora_fin' => 'required|after:hora_inicio',
                'estudiantes' => 'required|array|min:1',
                'estudiantes.*.id' => 'required|exists:estudiantes,id',
                'estudiantes.*.tarea' => 'required|string|max:255',
            ]);

            $validated['hora_inicio'] = Carbon::parse($validated['hora_inicio'])->format('H:i');
            $validated['hora_fin'] = Carbon::parse($validated['hora_fin'])->format('H:i');

            $esCoordinador = auth()->user()->hasRole('coordinador');
            $esProfesor = auth()->user()->hasRole('profesor');

            $clase = Horario::with(['asignacion.seccion', 'asignacion.materia'])
                ->findOrFail($validated['horario_id']);

            if ($esProfesor) {
                $profesor = auth()->user()->profesor;
                if ($clase->asignacion->profesor_id !== $profesor->id) {
                    return redirect()->route('limpiezas.index')
                        ->with('error', 'No tienes permiso para asignar limpiezas a esta clase');
                }
            } else if (!$esCoordinador) {
                return redirect()->route('limpiezas.index')
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            try {
                $limpieza = Limpieza::create([
                    'profesor_id' => $request->profesor_id, 
                    'horario_id' => $validated['horario_id'],
                    'fecha' => $validated['fecha'],
                    'hora_inicio' => $validated['hora_inicio'],
                    'hora_fin' => $validated['hora_fin'],
                    'estudiantes_tareas' => json_encode($validated['estudiantes']),
                ]);
            } catch (\Exception $e) {
                throw $e;
            }

            return redirect()->route('limpiezas.index')
                ->with('success', 'Limpieza asignada exitosamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la limpieza. Por favor, intenta nuevamente.');
        }
    }

    public function show(Limpieza $limpieza)
    {
        $limpieza->estudiantes_tareas = json_decode($limpieza->estudiantes_tareas, true) ?? [];
        
        $limpieza->load('horario.asignacion.seccion');
        
        // Obtener los IDs de estudiantes asignados a esta limpieza
        $idsEstudiantesAsignados = array_column($limpieza->estudiantes_tareas, 'id');
        
        // Obtener solo los estudiantes asignados a esta limpieza
        $estudiantes = $limpieza->horario->asignacion->seccion->estudiantes()
            ->whereIn('id', $idsEstudiantesAsignados)
            ->orderBy('apellidos')
            ->get();

        // Crear un array con los datos de tareas para cada estudiante
        $estudiantesConTareas = [];
        foreach ($estudiantes as $estudiante) {
            // Buscar la tarea por ID del estudiante
            $tareaData = null;
            foreach ($limpieza->estudiantes_tareas as $key => $tarea) {
                if (isset($tarea['id']) && $tarea['id'] == $estudiante->id) {
                    $tareaData = $tarea;
                    break;
                }
            }

            $estudiantesConTareas[] = [
                'estudiante' => $estudiante,
                'tarea' => $tareaData['tarea'] ?? '',
                'realizada' => isset($tareaData['realizada']) && $tareaData['realizada'] ? true : false,
                'observaciones' => $tareaData['observaciones'] ?? ''
            ];
        }
        
        return view('limpiezas.show', compact('limpieza', 'estudiantes', 'estudiantesConTareas'));
    }

    public function edit(Limpieza $limpieza)
    {
        if (auth()->user()->hasRole('profesor') && $limpieza->realizada) {
            return redirect()->route('limpiezas.index')
                ->with('error', 'No puedes editar una limpieza que ya ha sido completada');
        }

        // Log de los datos iniciales
        Log::info('Datos de limpieza:', [
            'id' => $limpieza->id,
            'estudiantes_tareas' => $limpieza->estudiantes_tareas
        ]);

        // Decodificar el JSON de estudiantes_tareas
        $limpieza->estudiantes_tareas = json_decode($limpieza->estudiantes_tareas, true) ?? [];
        Log::info('Después de decodificar JSON:', [
            'estudiantes_tareas' => $limpieza->estudiantes_tareas,
            'claves' => array_keys($limpieza->estudiantes_tareas)
        ]);

        $limpieza->load('horario.asignacion.seccion');
        
        // Obtener los estudiantes con sus datos completos
        $estudiantes = $limpieza->horario->asignacion->seccion->estudiantes()
            ->get()
            ->filter(function($estudiante) use ($limpieza) {
                return in_array($estudiante->id, array_column($limpieza->estudiantes_tareas, 'id'));
            })
            ->sortBy('apellidos');
        
        Log::info('Estudiantes filtrados:', [
            'count' => $estudiantes->count(),
            'estudiantes' => $estudiantes->pluck('id', 'nombres')->toArray()
        ]);

        // Pasar los datos de las tareas a la vista
        $tareasPorEstudiante = collect($limpieza->estudiantes_tareas)
            ->mapWithKeys(function($tareaData) {
                return [$tareaData['id'] => $tareaData];
            });
        
        Log::info('Tareas por estudiante:', [
            'tareas' => $tareasPorEstudiante->toArray()
        ]);

        return view('limpiezas.edit', compact('limpieza', 'estudiantes', 'tareasPorEstudiante'));

        Log::info('Estudiantes encontrados:', [
            'count' => $estudiantes->count(),
            'estudiantes' => $estudiantes->pluck('id', 'nombres')
        ]);

        return view('limpiezas.edit', compact('limpieza', 'estudiantes'));
    }

    public function update(Request $request, Limpieza $limpieza)
    {
        try {
            if ($limpieza->profesor_id !== auth()->user()->profesor->id) {
                return redirect()->back()->with('error', 'No tienes permisos para actualizar esta limpieza');
            }
            
            $request->validate([
                'fecha' => 'required|date',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                'realizada' => 'nullable|boolean',
                'observaciones' => 'nullable|string',
                'estudiantes_tareas' => 'required|array',
                'estudiantes_tareas.*.id' => 'required|exists:estudiantes,id',
                'estudiantes_tareas.*.tarea' => 'required|string|max:255',
                'estudiantes_tareas.*.realizada' => 'nullable|boolean',
                'estudiantes_tareas.*.observaciones' => 'nullable|string'
            ]);
            
            $estudiantesTareas = [];
            if ($request->has('estudiantes_tareas')) {
                foreach ($request->estudiantes_tareas as $id => $data) {
                    $realizada = isset($data['realizada']) && $data['realizada'] == '1';
                    
                    $estudiantesTareas[$id] = [
                        'id' => $id,
                        'tarea' => $data['tarea'],
                        'realizada' => $realizada,
                        'observaciones' => $data['observaciones'] ?? null
                    ];
                }
            }

            $limpieza->update([
                'fecha' => $request->fecha,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'realizada' => $request->has('realizada'),
                'observaciones' => $request->observaciones,
                'estudiantes_tareas' => json_encode($estudiantesTareas)
            ]);
            
            return redirect()->route('limpiezas.index')
                ->with('success', 'Limpieza actualizada exitosamente');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la limpieza: ' . $e->getMessage());
        }
    }

    public function destroy(Limpieza $limpieza)
    {
        try {
            $limpieza->delete();
            return redirect()->route('limpiezas.index')->with('success', 'Limpieza eliminada exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar la limpieza');
        }
    }

}