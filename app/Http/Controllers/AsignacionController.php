<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Profesor;
use App\Models\Materia;
use App\Models\Seccion;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AsignacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = auth()->user();

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
            \Log::info('Form data received:', $request->all());

            $validated = $request->validate([
                'profesor_id' => 'required|exists:profesores,id',
                'materia_id' => 'required|exists:materias,id',
                'seccion_id' => 'required|exists:secciones,id',
                'estudiantes_id' => 'required|array|min:1',
                'estudiantes_id.*' => 'exists:estudiantes,id'
            ], [
                'profesor_id.required' => 'El profesor es requerido',
                'profesor_id.exists' => 'El profesor seleccionado no existe',
                'materia_id.required' => 'La materia es requerida',
                'materia_id.exists' => 'La materia seleccionada no existe',
                'seccion_id.required' => 'La sección es requerida',
                'seccion_id.exists' => 'La sección seleccionada no existe',
                'estudiantes_id.required' => 'Debe seleccionar al menos un estudiante',
                'estudiantes_id.array' => 'Formato de estudiantes inválido',
                'estudiantes_id.min' => 'Debe seleccionar al menos un estudiante',
                'estudiantes_id.*.exists' => 'Uno o más estudiantes seleccionados no existen'
            ]);

            $asignacion = Asignacion::create([
                'profesor_id' => $validated['profesor_id'],
                'materia_id' => $validated['materia_id'],
                'seccion_id' => $validated['seccion_id'],
                'estudiantes_id' => json_encode($validated['estudiantes_id'])
            ]);

            return redirect()->route('asignaciones.index')
                ->with('success', 'Asignación creada exitosamente.');
        } catch (\Exception $e) {
            \Log::error('Error creating asignación: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

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
        return view('asignaciones.edit', compact('asignacion', 'profesores', 'materias', 'secciones'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asignacion $asignacion)
    {
        try {
            $request->validate([
                'profesor_id' => 'required|exists:profesores,id',
                'materia_id' => 'required|exists:materias,id',
                'seccion_id' => 'required|exists:secciones,id',
                'estudiantes_id' => 'required|array|min:1',
                'estudiantes_id.*' => 'exists:estudiantes,id',
            ], [
                'profesor_id.required' => 'El profesor es requerido',
                'profesor_id.exists' => 'El profesor seleccionado no existe',
                'materia_id.required' => 'La materia es requerida',
                'materia_id.exists' => 'La materia seleccionada no existe',
                'seccion_id.required' => 'La sección es requerida',
                'seccion_id.exists' => 'La sección seleccionada no existe',
                'estudiantes_id.required' => 'Debe seleccionar al menos un estudiante',
                'estudiantes_id.array' => 'Formato de estudiantes inválido',
                'estudiantes_id.min' => 'Debe seleccionar al menos un estudiante',
                'estudiantes_id.*.exists' => 'Uno o más estudiantes seleccionados no existen'
            ]);

            $asignacion->update([
                'profesor_id' => $request->profesor_id,
                'materia_id' => $request->materia_id,
                'seccion_id' => $request->seccion_id,
                'estudiantes_id' => json_encode($request->estudiantes_id)
            ]);

            return redirect()->route('asignaciones.index')
                ->with('success', 'Asignación actualizada exitosamente.');
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
                ->select('id', 'nombres', 'apellidos', 'codigo_estudiante', 'estado')
                ->orderBy('apellidos', 'asc')
                ->get()
                ->map(function ($estudiante) {
                    return [
                        'id' => $estudiante->id,
                        'nombre_completo' => $estudiante->nombres . ' ' . $estudiante->apellidos,
                        'cedula' => $estudiante->codigo_estudiante,
                        'estado' => $estudiante->estado
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
}
