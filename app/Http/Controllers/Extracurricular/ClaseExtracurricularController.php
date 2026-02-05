<?php

namespace App\Http\Controllers\Extracurricular;

use App\Http\Controllers\Controller;
use App\Models\ClaseExtracurricular;
use App\Models\Estudiante;
use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ClaseExtracurricularController extends Controller
{
    public function create()
    {
        $profesores = Profesor::with('user:id,name,tipo')
            ->whereHas('user', fn($query) => $query->where('tipo', 'profesor_extracurricular'))
            ->orderBy('id')
            ->get();

        $estudiantes = Estudiante::where('estado', 'activo')
            ->with(['seccion.grado'])
            ->orderBy('seccion_id')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return view('extracurricular.clases.create', [
            'profesores' => $profesores,
            'estudiantes' => $estudiantes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'profesor_id' => 'required|integer|exists:profesores,id',
            'aula' => 'nullable|string|max:255',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i',
            'dia_semana' => 'required|integer|between:1,7',
            'descripcion' => 'nullable|string',
            'estudiantes_id' => 'required|array|min:1',
            'estudiantes_id.*' => 'integer|exists:estudiantes,id',
        ]);

        $profesorEsExtracurricular = Profesor::query()
            ->whereKey($validated['profesor_id'])
            ->whereHas('user', fn($query) => $query->where('tipo', 'profesor_extracurricular'))
            ->exists();

        if (!$profesorEsExtracurricular) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['profesor_id' => 'El profesor seleccionado no es profesor extracurricular.']);
        }

        if (strtotime($validated['hora_fin']) <= strtotime($validated['hora_inicio'])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['hora_fin' => 'La hora fin debe ser mayor que la hora inicio.']);
        }

        $activoColumn = Schema::hasColumn('clases_extracurriculares', 'activo') ? 'activo' : 'activa';

        $clase = ClaseExtracurricular::create([
            'nombre' => $validated['nombre'],
            'profesor_id' => $validated['profesor_id'],
            'aula' => $validated['aula'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
            'hora_inicio' => $validated['hora_inicio'],
            'hora_fin' => $validated['hora_fin'],
            'dia_semana' => $validated['dia_semana'],
            $activoColumn => true,
            'created_by' => Auth::id(),
        ]);

        $clase->estudiantes()->sync($validated['estudiantes_id']);

        return redirect()->route('extracurricular.index')->with('success', 'Clase extracurricular creada');
    }

    public function edit(ClaseExtracurricular $clase)
    {
        $clase->load('estudiantes');

        $profesores = Profesor::with('user:id,name,tipo')
            ->whereHas('user', fn($query) => $query->where('tipo', 'profesor_extracurricular'))
            ->orderBy('id')
            ->get();

        $estudiantes = Estudiante::where('estado', 'activo')
            ->with(['seccion.grado'])
            ->orderBy('seccion_id')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return view('extracurricular.clases.edit', [
            'clase' => $clase,
            'profesores' => $profesores,
            'estudiantes' => $estudiantes,
            'seleccionados' => $clase->estudiantes->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, ClaseExtracurricular $clase)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'profesor_id' => 'required|integer|exists:profesores,id',
            'aula' => 'nullable|string|max:255',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i',
            'dia_semana' => 'required|integer|between:1,7',
            'descripcion' => 'nullable|string',
            'estudiantes_id' => 'required|array|min:1',
            'estudiantes_id.*' => 'integer|exists:estudiantes,id',
            'activo' => 'nullable|in:0,1',
        ]);

        $profesorEsExtracurricular = Profesor::query()
            ->whereKey($validated['profesor_id'])
            ->whereHas('user', fn($query) => $query->where('tipo', 'profesor_extracurricular'))
            ->exists();

        if (!$profesorEsExtracurricular) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['profesor_id' => 'El profesor seleccionado no es profesor extracurricular.']);
        }

        if (strtotime($validated['hora_fin']) <= strtotime($validated['hora_inicio'])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['hora_fin' => 'La hora fin debe ser mayor que la hora inicio.']);
        }

        $activoColumn = Schema::hasColumn('clases_extracurriculares', 'activo') ? 'activo' : 'activa';

        $clase->update([
            'nombre' => $validated['nombre'],
            'profesor_id' => $validated['profesor_id'],
            'aula' => $validated['aula'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
            'hora_inicio' => $validated['hora_inicio'],
            'hora_fin' => $validated['hora_fin'],
            'dia_semana' => $validated['dia_semana'],
            $activoColumn => $request->boolean('activo'),
        ]);

        $clase->estudiantes()->sync($validated['estudiantes_id']);

        return redirect()->route('extracurricular.index')->with('success', 'Clase extracurricular actualizada');
    }
}
