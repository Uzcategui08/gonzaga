<?php

namespace App\Http\Controllers\Extracurricular;

use App\Http\Controllers\Controller;
use App\Models\ClaseExtracurricular;
use App\Models\Estudiante;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ClaseExtracurricularController extends Controller
{
    private function assertClasesExtracurricularesColumns(array $requiredColumns): void
    {
        $missing = array_values(array_filter($requiredColumns, fn($col) => !Schema::hasColumn('clases_extracurriculares', $col)));

        if (!empty($missing)) {
            throw ValidationException::withMessages([
                'nombre' => 'Faltan columnas en la BD para guardar la clase: ' . implode(', ', $missing) . '. Ejecuta las migraciones.',
            ]);
        }
    }

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

        $this->assertClasesExtracurricularesColumns(['profesor_id', 'hora_inicio', 'hora_fin', 'dia_semana']);

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

        $data = [
            'nombre' => $validated['nombre'],
            $activoColumn => true,
            'created_by' => Auth::id(),
        ];

        if (Schema::hasColumn('clases_extracurriculares', 'profesor_id')) {
            $data['profesor_id'] = $validated['profesor_id'];
        }

        if (Schema::hasColumn('clases_extracurriculares', 'aula')) {
            $data['aula'] = $validated['aula'] ?? null;
        }

        if (Schema::hasColumn('clases_extracurriculares', 'descripcion')) {
            $data['descripcion'] = $validated['descripcion'] ?? null;
        }

        if (Schema::hasColumn('clases_extracurriculares', 'hora_inicio')) {
            $data['hora_inicio'] = $validated['hora_inicio'];
        }

        if (Schema::hasColumn('clases_extracurriculares', 'hora_fin')) {
            $data['hora_fin'] = $validated['hora_fin'];
        }

        if (Schema::hasColumn('clases_extracurriculares', 'dia_semana')) {
            $data['dia_semana'] = $validated['dia_semana'];
        }

        $clase = ClaseExtracurricular::create($data);

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

    public function show(ClaseExtracurricular $clase)
    {
        $usuario = Auth::user();
        $esProfesorExtracurricular = $usuario instanceof User
            ? $usuario->hasRole('profesor_extracurricular')
            : false;

        if ($esProfesorExtracurricular) {
            $profesorId = Profesor::where('user_id', $usuario->id)->value('id');
            if (!empty($profesorId) && (int) $clase->profesor_id !== (int) $profesorId) {
                abort(403, 'No tiene permiso para esta clase.');
            }
        }

        $clase->load([
            'profesor.user:id,name,tipo',
            'estudiantes.seccion.grado',
        ]);

        $estudiantes = $clase->estudiantes
            ->sortBy([
                fn($e) => $e->apellidos,
                fn($e) => $e->nombres,
            ])
            ->values();

        return view('extracurricular.clases.show', [
            'clase' => $clase,
            'estudiantes' => $estudiantes,
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

        $this->assertClasesExtracurricularesColumns(['profesor_id', 'hora_inicio', 'hora_fin', 'dia_semana']);

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

        $data = [
            'nombre' => $validated['nombre'],
            $activoColumn => $request->boolean('activo'),
        ];

        if (Schema::hasColumn('clases_extracurriculares', 'profesor_id')) {
            $data['profesor_id'] = $validated['profesor_id'];
        }

        if (Schema::hasColumn('clases_extracurriculares', 'aula')) {
            $data['aula'] = $validated['aula'] ?? null;
        }

        if (Schema::hasColumn('clases_extracurriculares', 'descripcion')) {
            $data['descripcion'] = $validated['descripcion'] ?? null;
        }

        if (Schema::hasColumn('clases_extracurriculares', 'hora_inicio')) {
            $data['hora_inicio'] = $validated['hora_inicio'];
        }

        if (Schema::hasColumn('clases_extracurriculares', 'hora_fin')) {
            $data['hora_fin'] = $validated['hora_fin'];
        }

        if (Schema::hasColumn('clases_extracurriculares', 'dia_semana')) {
            $data['dia_semana'] = $validated['dia_semana'];
        }

        $clase->update($data);

        $clase->estudiantes()->sync($validated['estudiantes_id']);

        return redirect()->route('extracurricular.index')->with('success', 'Clase extracurricular actualizada');
    }
}
