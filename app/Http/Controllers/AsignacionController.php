<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Profesor;
use App\Models\Materia;
use App\Models\Seccion;
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
            $asignaciones = Asignacion::with(['profesor.user', 'materia', 'seccion'])
                ->orderBy('id')
                ->get();
            
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

            $validated = $request->validate([
                'profesor_id' => 'required|exists:profesores,id',
                'materia_id' => 'required|exists:materias,id',
                'seccion_id' => 'required|exists:secciones,id',
            ], [
                'profesor_id.required' => 'El profesor es requerido',
                'profesor_id.exists' => 'El profesor seleccionado no existe',
                'materia_id.required' => 'La materia es requerida',
                'materia_id.exists' => 'La materia seleccionada no existe',
                'seccion_id.required' => 'La sección es requerida',
                'seccion_id.exists' => 'La sección seleccionada no existe',
            ]);

            $exists = Asignacion::where('materia_id', $request->materia_id)
                ->where('seccion_id', $request->seccion_id)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Esta materia ya tiene un profesor asignado en esta sección.');
            }

            $asignacion = Asignacion::create($validated);

            return redirect()->route('asignaciones.index')
                ->with('success', 'Asignación creada exitosamente.');

        } catch (\Exception $e) {
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

            $exists = Asignacion::where('materia_id', $request->materia_id)
                ->where('seccion_id', $request->seccion_id)
                ->where('id', '!=', $asignacion->id)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Esta materia ya tiene un profesor asignado en esta sección.');
            }

            $request->validate([
                'profesor_id' => 'required|exists:profesores,id',
                'materia_id' => 'required|exists:materias,id',
                'seccion_id' => 'required|exists:secciones,id',
            ], [
                'profesor_id.required' => 'El profesor es requerido',
                'profesor_id.exists' => 'El profesor seleccionado no existe',
                'materia_id.required' => 'La materia es requerida',
                'materia_id.exists' => 'La materia seleccionada no existe',
                'seccion_id.required' => 'La sección es requerida',
                'seccion_id.exists' => 'La sección seleccionada no existe',
            ]);

            $asignacion->update($request->only(['profesor_id', 'materia_id', 'seccion_id']));
            
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
}
