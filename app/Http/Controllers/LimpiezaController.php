<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Limpieza;
use App\Models\Horario;
use App\Models\Aula;
use App\Models\Estudiante;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $esCoordinador = $user && $user->hasRole('coordinador');
        $esProfesor = $user && $user->hasRole('profesor');
        $esAdmin = $user && $user->hasRole('admin');

        if ($esCoordinador) {
            $secciones = $user->secciones;

            $clasesHoy = Horario::where('dia', $diaActual)
                ->whereHas('asignacion', function ($query) use ($secciones) {
                    $query->whereIn('seccion_id', $secciones->pluck('id'));
                })
                ->with(['asignacion.materia', 'asignacion.seccion', 'asignacion.seccion.grado', 'asignacion.profesor'])
                ->get();
        } else if ($esProfesor) {
            $profesor = $user->profesor;
            if ($profesor) {
                $clasesHoy = Horario::where('dia', $diaActual)
                    ->whereHas('asignacion', function ($query) use ($profesor) {
                        $query->where('profesor_id', $profesor->id);
                    })
                    ->with(['asignacion.materia', 'asignacion.seccion', 'asignacion.seccion.grado', 'asignacion.profesor'])
                    ->get();

                // Crear automáticamente limpieza para la última clase del día del profesor si no existe
                if ($clasesHoy->isNotEmpty()) {
                    $ultimaClase = $clasesHoy->sortBy(function ($h) {
                        return Carbon::parse($h->hora_fin);
                    })->last();

                    if ($ultimaClase) {
                        $limpiezaExistente = Limpieza::whereDate('fecha', $fechaActual)
                            ->where('horario_id', $ultimaClase->id)
                            ->where('profesor_id', $profesor->id)
                            ->first();

                        if (!$limpiezaExistente) {
                            $this->crearLimpiezaRotativa($profesor->id, $ultimaClase, $fechaActual);
                        }
                    }
                }
            } else {
                // Profesor con rol pero sin perfil asociado
                $clasesHoy = collect();
            }
        } else {
            $clasesHoy = Horario::where('dia', $diaActual)
                ->with(['asignacion.materia', 'asignacion.seccion', 'asignacion.seccion.grado', 'asignacion.profesor'])
                ->get();
        }

        if ($esCoordinador) {

            $secciones = $user->secciones;

            $limpiezas = Limpieza::whereDate('fecha', $fechaActual)
                ->whereHas('profesor.asignaciones', function ($query) use ($secciones) {
                    $query->whereIn('seccion_id', $secciones->pluck('id'));
                })
                ->with(['profesor.usuario'])
                ->orderBy('fecha', 'desc')
                ->get();
        } else {
            $profesor = $user->profesor;
            if ($esProfesor && $profesor) {
                $limpiezas = Limpieza::where('profesor_id', $profesor->id)
                    ->whereDate('fecha', $fechaActual)
                    ->with(['profesor.usuario'])
                    ->orderBy('fecha', 'desc')
                    ->get();
            } else {
                $limpiezas = collect();
            }
        }

        // Construir resumen por sección para admin: estudiantes asignados hoy
        $limpiezasSecciones = collect();
        if ($esAdmin) {
            $horariosDia = Horario::where('dia', $diaActual)
                ->with([
                    'asignacion.seccion.grado',
                    'asignacion.seccion.estudiantes',
                    'asignacion.profesor.usuario'
                ])->get();

            // Tomar el último horario del día por sección
            $ultimoHorarioPorSeccion = $horariosDia
                ->filter(function ($h) {
                    return optional($h->asignacion)->seccion;
                })
                ->groupBy(function ($h) {
                    return $h->asignacion->seccion->id;
                })
                ->map(function ($lista) {
                    return $lista->sortBy(function ($h) {
                        return Carbon::parse($h->hora_fin);
                    })->last();
                });

            $limpiezasSecciones = $ultimoHorarioPorSeccion->map(function ($horario) {
                $seccion = $horario->asignacion->seccion;
                $estudiantes = $seccion->estudiantes->sortBy(['apellidos', 'nombres'])->values();
                $total = $estudiantes->count();

                // offset por número de limpiezas realizadas históricas de esa sección
                $completadas = Limpieza::where('realizada', true)
                    ->whereHas('horario.asignacion', function ($q) use ($seccion) {
                        $q->where('seccion_id', $seccion->id);
                    })
                    ->count();
                $offset = $total > 0 ? ($completadas % $total) : 0;

                $seleccion = collect();
                for ($i = 0; $i < min(3, $total); $i++) {
                    $idx = ($offset + $i) % $total;
                    $alumno = $estudiantes[$idx];
                    $seleccion->push([
                        'id' => $alumno->id,
                        'nombre' => $alumno->apellidos_nombres,
                        'tarea' => 'Limpieza',
                        'realizada' => false,
                        'observaciones' => null,
                    ]);
                }

                $profesor = optional($horario->asignacion->profesor)->usuario;
                $item = [
                    'seccion_id' => $seccion->id,
                    'seccion_nombre' => $seccion->grado->nombre . ' - ' . $seccion->nombre,
                    'profesor' => $profesor ? $profesor->name : 'Sin profesor',
                    'hora' => Carbon::parse($horario->hora_inicio)->format('H:i') . ' - ' . Carbon::parse($horario->hora_fin)->format('H:i'),
                    'horario_id' => $horario->id,
                    'realizada' => false,
                    'estudiantes' => $seleccion,
                ];
                // Devolver como colección con un único elemento para que el blade pueda iterar
                return collect([$item]);
            });
        }

        // También preparar mapa de limpiezas de hoy por horario para estado del botón en vista admin
        $limpiezasHoyPorHorario = Limpieza::whereDate('fecha', $fechaActual)->get()->keyBy('horario_id');

        return view('limpiezas.index', compact('clasesHoy', 'limpiezas', 'esCoordinador', 'esProfesor', 'esAdmin', 'limpiezasSecciones', 'limpiezasHoyPorHorario'));
    }

    /**
     * Materializar una limpieza desde el panel admin para un horario dado (última clase de sección)
     */
    public function materializar(Request $request, Horario $horario)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        // Validar rol admin usando permisos/roles disponibles
        if (!$user || !$user->hasRole('admin')) {
            abort(403);
        }

        $fechaActual = Carbon::now('America/Caracas');
        // Verificar si ya existe limpieza para ese horario hoy
        $existente = Limpieza::whereDate('fecha', $fechaActual)
            ->where('horario_id', $horario->id)
            ->first();
        if ($existente) {
            return redirect()->route('limpiezas.index')
                ->with('status', 'Ya existe una limpieza para ese horario hoy.');
        }

        $asignacion = $horario->asignacion;
        if (!$asignacion || !$asignacion->seccion) {
            return redirect()->route('limpiezas.index')
                ->with('error', 'Horario sin sección asociada.');
        }

        $seccion = $asignacion->seccion;
        $estudiantes = $seccion->estudiantes->sortBy(['apellidos', 'nombres'])->values();
        $total = $estudiantes->count();
        if ($total === 0) {
            return redirect()->route('limpiezas.index')
                ->with('error', 'La sección no tiene estudiantes.');
        }

        // offset por número de limpiezas completadas históricas de la sección
        $completadas = Limpieza::where('realizada', true)
            ->whereHas('horario.asignacion', function ($q) use ($seccion) {
                $q->where('seccion_id', $seccion->id);
            })
            ->count();
        $offset = $total > 0 ? ($completadas % $total) : 0;

        $seleccion = [];
        for ($i = 0; $i < min(3, $total); $i++) {
            $idx = ($offset + $i) % $total;
            $alumno = $estudiantes[$idx];
            $seleccion[] = [
                'id' => $alumno->id,
                'nombre' => $alumno->apellidos_nombres,
                'tarea' => 'Limpieza',
                'realizada' => false,
                'observaciones' => null,
            ];
        }

        Limpieza::create([
            'fecha' => $fechaActual->toDateString(),
            'horario_id' => $horario->id,
            'profesor_id' => optional($asignacion->profesor)->id,
            'hora_inicio' => Carbon::parse($horario->hora_inicio)->format('H:i'),
            'hora_fin' => Carbon::parse($horario->hora_fin)->format('H:i'),
            'estudiantes_tareas' => json_encode($seleccion),
            'realizada' => false,
        ]);

        return redirect()->route('limpiezas.index')
            ->with('success', 'Limpieza creada correctamente.');
    }

    public function create(Request $request, $id = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Por favor, inicie sesión primero');
        }

        try {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            $esCoordinador = $user && $user->hasRole('coordinador');
            $esProfesor = $user && $user->hasRole('profesor');

            if ($esProfesor) {
                $profesor = $user->profesor;
                if (!$profesor) {
                    return redirect()->back()->with('error', 'No tienes un perfil de profesor asociado');
                }

                $diaActual = Carbon::now('America/Caracas')->format('l');
                $clasesProfesor = Horario::whereHas('asignacion', function ($query) use ($profesor) {
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
                            ->map(function ($estudianteData) use ($estudiantesDeSeccion) {
                                return $estudiantesDeSeccion->firstWhere('id', $estudianteData['id']);
                            })
                            ->filter();
                    }

                    $estudiantesDisponibles = $estudiantesDeSeccion->filter(function ($estudiante) use ($estudiantes) {
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

            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            $esCoordinador = $user && $user->hasRole('coordinador');
            $esProfesor = $user && $user->hasRole('profesor');

            $clase = Horario::with(['asignacion.seccion', 'asignacion.materia'])
                ->findOrFail($validated['horario_id']);

            if ($esProfesor) {
                $profesor = $user->profesor;
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

        $idsEstudiantesAsignados = array_column($limpieza->estudiantes_tareas, 'id');

        $estudiantes = $limpieza->horario->asignacion->seccion->estudiantes()
            ->whereIn('id', $idsEstudiantesAsignados)
            ->orderBy('apellidos')
            ->get();

        $estudiantesConTareas = [];
        foreach ($estudiantes as $estudiante) {

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
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && $user->hasRole('profesor') && $limpieza->realizada) {
            return redirect()->route('limpiezas.index')
                ->with('error', 'No puedes editar una limpieza que ya ha sido completada');
        }

        Log::info('Datos de limpieza:', [
            'id' => $limpieza->id,
            'estudiantes_tareas' => $limpieza->estudiantes_tareas
        ]);

        $limpieza->estudiantes_tareas = json_decode($limpieza->estudiantes_tareas, true) ?? [];
        Log::info('Después de decodificar JSON:', [
            'estudiantes_tareas' => $limpieza->estudiantes_tareas,
            'claves' => array_keys($limpieza->estudiantes_tareas)
        ]);

        $limpieza->load('horario.asignacion.seccion');

        $estudiantes = $limpieza->horario->asignacion->seccion->estudiantes()
            ->get()
            ->filter(function ($estudiante) use ($limpieza) {
                return in_array($estudiante->id, array_column($limpieza->estudiantes_tareas, 'id'));
            })
            ->sortBy('apellidos');

        Log::info('Estudiantes filtrados:', [
            'count' => $estudiantes->count(),
            'estudiantes' => $estudiantes->pluck('id', 'nombres')->toArray()
        ]);

        $tareasPorEstudiante = collect($limpieza->estudiantes_tareas)
            ->mapWithKeys(function ($tareaData) {
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
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            if (!$user || !$user->profesor || $limpieza->profesor_id !== $user->profesor->id) {
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

    /**
     * Crea una limpieza con 3 estudiantes por rotación en la sección de la clase indicada.
     * Avanza la rotación sólo en base a limpiezas realizadas (realizada = true).
     */
    private function crearLimpiezaRotativa(int $profesorId, Horario $horario, Carbon $fechaActual): void
    {
        // Obtener estudiantes ordenados por apellidos en la sección
        $seccion = optional($horario->asignacion)->seccion;
        if (!$seccion) {
            return;
        }

        $estudiantes = Estudiante::where('seccion_id', $seccion->id)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        if ($estudiantes->isEmpty()) {
            return;
        }

        // Contar limpiezas realizadas para esta sección (rotación avanza sólo con realizadas)
        $completadas = Limpieza::where('realizada', true)
            ->whereHas('horario.asignacion', function ($q) use ($seccion) {
                $q->where('seccion_id', $seccion->id);
            })
            ->count();

        $total = $estudiantes->count();
        $offset = $total > 0 ? ($completadas % $total) : 0;

        // Seleccionar 3 estudiantes en orden circular
        $seleccion = [];
        for ($i = 0; $i < min(3, $total); $i++) {
            $idx = ($offset + $i) % $total;
            $alumno = $estudiantes[$idx];
            $seleccion[] = [
                'id' => $alumno->id,
                'tarea' => 'Limpieza general',
                'realizada' => false,
                'observaciones' => null,
            ];
        }

        // Crear registro de limpieza inicial (no realizada)
        Limpieza::create([
            'profesor_id' => $profesorId,
            'horario_id' => $horario->id,
            'fecha' => $fechaActual->toDateString(),
            'hora_inicio' => Carbon::parse($horario->hora_inicio)->format('H:i'),
            'hora_fin' => Carbon::parse($horario->hora_fin)->format('H:i'),
            'realizada' => false,
            'estudiantes_tareas' => json_encode($seleccion),
            'observaciones' => null,
        ]);
    }
}
