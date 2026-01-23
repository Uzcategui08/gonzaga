<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Profesor;
use App\Models\Materia;
use App\Models\Seccion;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AsignacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = Auth::user();

            if ($user->hasRole('coordinador')) {
                $seccionesCoordinador = $user->secciones->pluck('id');
                $asignaciones = Asignacion::with(['profesor.user', 'materia', 'seccion'])
                    ->whereIn('seccion_id', $seccionesCoordinador)
                    ->orderBy('id')
                    ->get();
            } else {
                $asignaciones = Asignacion::with(['profesor.user', 'materia', 'seccion'])
                    ->orderBy('id')
                    ->get();
            }

            return view('asignaciones.index', [
                'asignaciones' => $asignaciones
            ]);
        } catch (\Exception $e) {
            Log::error('Error en AsignacionController@index: ' . $e->getMessage());
            return view('asignaciones.index')->with('error', 'Error al cargar las asignaciones: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $profesores = Profesor::with('user')->get();
        $materias = Materia::with('grados')->get();
        $secciones = Seccion::all();
        return view('asignaciones.create', compact('profesores', 'materias', 'secciones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Log::info('AsignacionController@store request summary', [
                'user_id' => optional($request->user())->id,
                'nivel' => $request->input('nivel'),
                'profesor_id' => $request->input('profesor_id'),
                'aplicar_todas_materias' => $request->boolean('aplicar_todas_materias'),
                'aplicar_todas_secciones' => $request->boolean('aplicar_todas_secciones'),
                'aplicar_todos_estudiantes' => $request->boolean('aplicar_todos_estudiantes'),
                'materias_count' => is_array($request->input('materias_id')) ? count($request->input('materias_id')) : 0,
                'secciones_count' => is_array($request->input('secciones_id')) ? count($request->input('secciones_id')) : 0,
                'estudiantes_count' => is_array($request->input('estudiantes_id')) ? count($request->input('estudiantes_id')) : 0,
            ]);

            $validated = $request->validate([
                'nivel' => 'nullable|in:primaria,secundaria',
                'profesor_id' => 'required|exists:profesores,id',
                'materias_id' => 'required_without:aplicar_todas_materias|array|min:1',
                'materias_id.*' => 'exists:materias,id',
                'aplicar_todas_materias' => 'nullable',
                'secciones_id' => 'required_without:aplicar_todas_secciones|array|min:1',
                'secciones_id.*' => 'exists:secciones,id',
                'aplicar_todas_secciones' => 'nullable',
                'aplicar_todos_estudiantes' => 'nullable|boolean',
                'estudiantes_id' => 'required_unless:aplicar_todos_estudiantes,1|array|min:1',
                'estudiantes_id.*' => 'exists:estudiantes,id'
            ], [
                'profesor_id.required' => 'El profesor es requerido',
                'profesor_id.exists' => 'El profesor seleccionado no existe',
                'materias_id.required_without' => 'Debe seleccionar al menos una materia',
                'materias_id.array' => 'Formato de materias inválido',
                'materias_id.min' => 'Debe seleccionar al menos una materia',
                'materias_id.*.exists' => 'Una o más materias seleccionadas no existen',
                'secciones_id.required_without' => 'Debe seleccionar al menos una sección',
                'secciones_id.array' => 'Formato de secciones inválido',
                'secciones_id.min' => 'Debe seleccionar al menos una sección',
                'secciones_id.*.exists' => 'Una o más secciones seleccionadas no existen',
                'estudiantes_id.required_unless' => 'Debe seleccionar al menos un estudiante',
                'estudiantes_id.array' => 'Formato de estudiantes inválido',
                'estudiantes_id.min' => 'Debe seleccionar al menos un estudiante',
                'estudiantes_id.*.exists' => 'Uno o más estudiantes seleccionados no existen'
            ]);

            $aplicarTodas = $request->boolean('aplicar_todas_secciones');
            $aplicarTodasMaterias = $request->boolean('aplicar_todas_materias');

            $materiaIds = $aplicarTodasMaterias
                ? Materia::query()->pluck('id')->map(fn($v) => (int) $v)->values()->all()
                : collect($validated['materias_id'] ?? [])->map(fn($v) => (int) $v)->unique()->values()->all();

            if (empty($materiaIds)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Debe seleccionar al menos una materia.');
            }

            // Determinar secciones destino (seleccionadas o todas las del profesor).
            if ($aplicarTodas) {
                $nivel = ($validated['nivel'] ?? null) ? strtolower((string) $validated['nivel']) : null;

                $seccionesQuery = Seccion::query();
                if ($nivel) {
                    $seccionesQuery->whereHas('grado', fn($q) => $q->where('nivel', $nivel));
                }

                $seccionIds = $seccionesQuery->pluck('id')
                    ->map(fn($v) => (int) $v)
                    ->values()
                    ->all();
            } else {
                $seccionIds = collect($validated['secciones_id'] ?? [])
                    ->map(fn($v) => (int) $v)
                    ->unique()
                    ->values()
                    ->all();
            }

            if (empty($seccionIds)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No hay secciones disponibles para aplicar la asignación.');
            }

            $aplicarTodosEstudiantes = $request->boolean('aplicar_todos_estudiantes');
            if ($aplicarTodosEstudiantes) {
                $estudiantes = Estudiante::whereIn('seccion_id', $seccionIds)
                    ->get(['id', 'seccion_id']);
            } else {
                $selectedIds = $validated['estudiantes_id'];
                $estudiantes = Estudiante::whereIn('id', $selectedIds)
                    ->whereIn('seccion_id', $seccionIds)
                    ->get(['id', 'seccion_id']);
            }

            $idsPorSeccion = $estudiantes
                ->groupBy('seccion_id')
                ->map(fn($rows) => $rows->pluck('id')->values()->all());

            if ($idsPorSeccion->isEmpty()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No se encontraron estudiantes seleccionados dentro de las secciones seleccionadas.');
            }

            $created = 0;
            $skippedPairs = 0;

            foreach ($materiaIds as $materiaId) {
                $materiaId = (int) $materiaId;
                foreach ($idsPorSeccion as $seccionId => $ids) {
                    $seccionId = (int) $seccionId;

                    $exists = Asignacion::where('materia_id', $materiaId)
                        ->where('seccion_id', $seccionId)
                        ->exists();

                    if ($exists) {
                        $skippedPairs++;
                        continue;
                    }

                    Asignacion::create([
                        'profesor_id' => $validated['profesor_id'],
                        'materia_id' => $materiaId,
                        'seccion_id' => $seccionId,
                        'estudiantes_id' => json_encode($ids)
                    ]);
                    $created++;
                }
            }

            $msg = $created === 1
                ? 'Asignación creada exitosamente.'
                : "Asignaciones creadas exitosamente ({$created}).";

            if ($skippedPairs > 0) {
                $msg .= " Se omitieron {$skippedPairs} combinaciones (materia/sección) ya existentes.";
            }

            return redirect()->route('asignaciones.index')
                ->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('Error creating asignación: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la asignación: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Asignacion $asignacion)
    {
        return view('asignaciones.show', compact('asignacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asignacion $asignacion)
    {
        $profesores = Profesor::with('user')->get();
        $materias = Materia::with('grados')->get();
        $secciones = Seccion::all();

        $seleccionPorSeccion = Asignacion::where('profesor_id', $asignacion->profesor_id)
            ->where('materia_id', $asignacion->materia_id)
            ->get(['seccion_id', 'estudiantes_id'])
            ->mapWithKeys(function ($a) {
                $ids = $a->estudiantes_id ? json_decode($a->estudiantes_id, true) : [];
                $ids = is_array($ids) ? array_map('strval', $ids) : [];
                return [$a->seccion_id => $ids];
            })
            ->toArray();

        return view('asignaciones.edit', compact('asignacion', 'profesores', 'materias', 'secciones', 'seleccionPorSeccion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asignacion $asignacion)
    {
        try {
            $validated = $request->validate([
                'profesor_id' => 'required|exists:profesores,id',
                'materia_id' => 'required|exists:materias,id',
                'seccion_id' => 'required_without:aplicar_todas_secciones|exists:secciones,id',
                'aplicar_todas_secciones' => 'nullable',
                'estudiantes_id' => 'required|array|min:1',
                'estudiantes_id.*' => 'exists:estudiantes,id',
            ], [
                'profesor_id.required' => 'El profesor es requerido',
                'profesor_id.exists' => 'El profesor seleccionado no existe',
                'materia_id.required' => 'La materia es requerida',
                'materia_id.exists' => 'La materia seleccionada no existe',
                'seccion_id.required_without' => 'La sección es requerida',
                'seccion_id.exists' => 'La sección seleccionada no existe',
                'estudiantes_id.required' => 'Debe seleccionar al menos un estudiante',
                'estudiantes_id.array' => 'Formato de estudiantes inválido',
                'estudiantes_id.min' => 'Debe seleccionar al menos un estudiante',
                'estudiantes_id.*.exists' => 'Uno o más estudiantes seleccionados no existen'
            ]);

            $aplicarTodas = $request->boolean('aplicar_todas_secciones');
            if (!$aplicarTodas) {
                $asignacion->update([
                    'profesor_id' => $validated['profesor_id'],
                    'materia_id' => $validated['materia_id'],
                    'seccion_id' => $validated['seccion_id'],
                    'estudiantes_id' => json_encode($validated['estudiantes_id'])
                ]);

                return redirect()->route('asignaciones.index')
                    ->with('success', 'Asignación actualizada exitosamente.');
            }

            $nivelMateria = Materia::query()->whereKey($validated['materia_id'])->value('nivel');
            $nivelMateria = $nivelMateria ? strtolower((string) $nivelMateria) : null;

            $seccionesQuery = Seccion::query();
            if ($nivelMateria) {
                $seccionesQuery->whereHas('grado', fn($q) => $q->where('nivel', $nivelMateria));
            }

            $seccionIds = $seccionesQuery->pluck('id')
                ->map(fn($v) => (int) $v)
                ->values()
                ->all();

            if (empty($seccionIds)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No hay secciones disponibles para aplicar la asignación.');
            }

            $selectedIds = $validated['estudiantes_id'];
            $estudiantes = Estudiante::whereIn('id', $selectedIds)
                ->whereIn('seccion_id', $seccionIds)
                ->get(['id', 'seccion_id']);

            $idsPorSeccion = $estudiantes
                ->groupBy('seccion_id')
                ->map(fn($rows) => $rows->pluck('id')->values()->all());

            if ($idsPorSeccion->isEmpty()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No se encontraron estudiantes seleccionados dentro de las secciones del profesor.');
            }

            foreach ($idsPorSeccion as $seccionId => $ids) {
                Asignacion::updateOrCreate(
                    [
                        'profesor_id' => $validated['profesor_id'],
                        'materia_id' => $validated['materia_id'],
                        'seccion_id' => $seccionId,
                    ],
                    [
                        'estudiantes_id' => json_encode($ids),
                    ]
                );
            }

            return redirect()->route('asignaciones.index')
                ->with('success', 'Asignaciones actualizadas exitosamente para las secciones del profesor.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la asignación: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asignacion $asignacion)
    {
        try {
            $asignacion->delete();
            return redirect()->route('asignaciones.index')
                ->with('success', 'Asignación eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la asignación: ' . $e->getMessage());
        }
    }

    /**
     * Get students by section
     */
    public function getEstudiantesBySeccion(Request $request)
    {
        try {
            $request->validate([
                'seccion_id' => 'required|exists:secciones,id'
            ]);

            $estudiantes = Estudiante::where('seccion_id', $request->seccion_id)
                ->select('id', 'seccion_id', 'nombres', 'apellidos', 'codigo_estudiante', 'estado', 'genero')
                ->orderBy('apellidos', 'asc')
                ->get()
                ->map(function ($estudiante) {
                    return [
                        'id' => $estudiante->id,
                        'seccion_id' => $estudiante->seccion_id,
                        'nombre_completo' => $estudiante->nombres . ' ' . $estudiante->apellidos,
                        'cedula' => $estudiante->codigo_estudiante,
                        'estado' => $estudiante->estado,
                        'genero' => $estudiante->genero,
                    ];
                });

            return response()->json([
                'success' => true,
                'estudiantes' => $estudiantes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los estudiantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get students by profesor (por defecto: secciones donde es titular o ya asignadas).
     * Si se envía todas_secciones=1, devuelve secciones/estudiantes sin depender de titularidad.
     */
    public function getEstudiantesByProfesor(Request $request)
    {
        try {
            $request->validate([
                'profesor_id' => 'required|exists:profesores,id',
                'todas_secciones' => 'nullable|boolean',
                'nivel' => 'nullable|in:primaria,secundaria',
            ]);

            $todasSecciones = $request->boolean('todas_secciones');
            $nivel = $request->filled('nivel') ? strtolower((string) $request->input('nivel')) : null;

            if ($todasSecciones) {
                $seccionesQuery = Seccion::with(['grado'])
                    ->orderBy('nombre');
                if ($nivel) {
                    $seccionesQuery->whereHas('grado', fn($q) => $q->where('nivel', $nivel));
                }

                $secciones = $seccionesQuery->get(['id', 'nombre', 'grado_id']);

                if ($secciones->isEmpty()) {
                    return response()->json([
                        'success' => true,
                        'secciones' => [],
                        'estudiantes' => []
                    ]);
                }

                $seccionIds = $secciones->pluck('id')->all();
                $seccionNombre = $secciones->mapWithKeys(fn($s) => [$s->id => $s->nombre]);

                $estudiantes = Estudiante::whereIn('seccion_id', $seccionIds)
                    ->select('id', 'seccion_id', 'nombres', 'apellidos', 'codigo_estudiante', 'estado', 'genero')
                    ->orderBy('seccion_id')
                    ->orderBy('apellidos', 'asc')
                    ->get()
                    ->map(function ($estudiante) use ($seccionNombre) {
                        return [
                            'id' => $estudiante->id,
                            'seccion_id' => $estudiante->seccion_id,
                            'seccion_nombre' => $seccionNombre[$estudiante->seccion_id] ?? null,
                            'nombre_completo' => $estudiante->nombres . ' ' . $estudiante->apellidos,
                            'cedula' => $estudiante->codigo_estudiante,
                            'estado' => $estudiante->estado,
                            'genero' => $estudiante->genero,
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'secciones' => $secciones->map(fn($s) => [
                        'id' => $s->id,
                        'nombre' => $s->nombre,
                        'grado' => $s->grado?->nombre,
                        'nivel' => $s->grado?->nivel,
                    ]),
                    'estudiantes' => $estudiantes
                ]);
            }

            $seccionIds = Seccion::where('titular_profesor_id', $request->profesor_id)->pluck('id')
                ->merge(Asignacion::where('profesor_id', $request->profesor_id)->pluck('seccion_id'))
                ->unique()
                ->values();

            $seccionesQuery = Seccion::with(['grado'])
                ->whereIn('id', $seccionIds)
                ->orderBy('nombre');
            if ($nivel) {
                $seccionesQuery->whereHas('grado', fn($q) => $q->where('nivel', $nivel));
            }

            $secciones = $seccionesQuery->get(['id', 'nombre', 'grado_id']);

            if ($secciones->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'secciones' => [],
                    'estudiantes' => []
                ]);
            }

            $seccionIds = $secciones->pluck('id')->all();
            $seccionNombre = $secciones->mapWithKeys(fn($s) => [$s->id => $s->nombre]);

            $estudiantes = Estudiante::whereIn('seccion_id', $seccionIds)
                ->select('id', 'seccion_id', 'nombres', 'apellidos', 'codigo_estudiante', 'estado', 'genero')
                ->orderBy('seccion_id')
                ->orderBy('apellidos', 'asc')
                ->get()
                ->map(function ($estudiante) use ($seccionNombre) {
                    return [
                        'id' => $estudiante->id,
                        'seccion_id' => $estudiante->seccion_id,
                        'seccion_nombre' => $seccionNombre[$estudiante->seccion_id] ?? null,
                        'nombre_completo' => $estudiante->nombres . ' ' . $estudiante->apellidos,
                        'cedula' => $estudiante->codigo_estudiante,
                        'estado' => $estudiante->estado,
                        'genero' => $estudiante->genero,
                    ];
                });

            return response()->json([
                'success' => true,
                'secciones' => $secciones->map(fn($s) => [
                    'id' => $s->id,
                    'nombre' => $s->nombre,
                    'grado' => $s->grado?->nombre,
                    'nivel' => $s->grado?->nivel,
                ]),
                'estudiantes' => $estudiantes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los estudiantes: ' . $e->getMessage()
            ], 500);
        }
    }
}
