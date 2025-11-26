<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Seccion;
use App\Models\Asignacion;
use App\Models\Horario;
use App\Models\Grado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EstudianteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $nivel = $request->input('nivel');

            $estudiantesQuery = Estudiante::with(['seccion.grado'])
                ->orderBy('apellidos')
                ->orderBy('nombres');

            if ($nivel) {
                $estudiantesQuery->whereHas('seccion.grado', function ($query) use ($nivel) {
                    $query->where('nivel', $nivel);
                });
            }

            $estudiantes = $estudiantesQuery->get();

            $niveles = Grado::select('nivel')
                ->whereNotNull('nivel')
                ->distinct()
                ->orderBy('nivel')
                ->pluck('nivel');

            return view('estudiantes.index', [
                'estudiantes' => $estudiantes,
                'niveles' => $niveles,
                'nivelSeleccionado' => $nivel,
            ]);
        } catch (\Exception $e) {
            return view('estudiantes.index')->with('error', 'Error al cargar los estudiantes: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $secciones = Seccion::all();
        return view('estudiantes.create', compact('secciones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'codigo_estudiante' => 'required|unique:estudiantes|string|max:20',
                'nombres' => 'required|string|max:100',
                'apellidos' => 'required|string|max:100',
                'fecha_nacimiento' => 'required|date',
                'genero' => 'required|string|in:M,F',
                'seccion_id' => 'required|exists:secciones,id',
                'fecha_ingreso' => 'required|date',
                'contacto_emergencia_nombre' => 'required|string|max:100',
                'contacto_emergencia_parentesco' => 'required|string|max:50',
                'contacto_emergencia_telefono' => 'required|string|max:20',
                'direccion' => 'nullable|string|max:255',
                'observaciones' => 'nullable|string',
            ], [
                'codigo_estudiante.required' => 'El código del estudiante es requerido',
                'codigo_estudiante.unique' => 'Este código de estudiante ya existe',
                'nombres.required' => 'Los nombres son requeridos',
                'apellidos.required' => 'Los apellidos son requeridos',
                'fecha_nacimiento.required' => 'La fecha de nacimiento es requerida',
                'genero.required' => 'El género es requerido',
                'seccion_id.required' => 'La sección es requerida',
                'fecha_ingreso.required' => 'La fecha de ingreso es requerida',
                'contacto_emergencia_nombre.required' => 'El nombre del contacto de emergencia es requerido',
                'contacto_emergencia_parentesco.required' => 'El parentesco del contacto de emergencia es requerido',
                'contacto_emergencia_telefono.required' => 'El teléfono del contacto de emergencia es requerido',
            ]);

            $estudiante = Estudiante::create($validated);

            return redirect()->route('estudiantes.index')
                ->with('success', 'Estudiante creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Estudiante $estudiante)
    {
        $estudiante->load('seccion');
        return view('estudiantes.show', compact('estudiante'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Estudiante $estudiante)
    {
        $secciones = Seccion::all();
        return view('estudiantes.edit', compact('estudiante', 'secciones'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Estudiante $estudiante)
    {
        try {
            $validated = $request->validate([
                'codigo_estudiante' => 'required|unique:estudiantes,codigo_estudiante,' . $estudiante->id,
                'nombres' => 'required|string|max:100',
                'apellidos' => 'required|string|max:100',
                'fecha_nacimiento' => 'required|date',
                'genero' => 'required|string|in:M,F',
                'seccion_id' => 'required|exists:secciones,id',
                'fecha_ingreso' => 'required|date',
                'contacto_emergencia_nombre' => 'required|string|max:100',
                'contacto_emergencia_parentesco' => 'required|string|max:50',
                'contacto_emergencia_telefono' => 'required|string|max:20',
                'direccion' => 'nullable|string|max:255',
                'observaciones' => 'nullable|string',
            ], [
                'codigo_estudiante.required' => 'El código del estudiante es requerido',
                'codigo_estudiante.unique' => 'Este código de estudiante ya existe',
                'nombres.required' => 'Los nombres son requeridos',
                'apellidos.required' => 'Los apellidos son requeridos',
                'fecha_nacimiento.required' => 'La fecha de nacimiento es requerida',
                'genero.required' => 'El género es requerido',
                'seccion_id.required' => 'La sección es requerida',
                'fecha_ingreso.required' => 'La fecha de ingreso es requerida',
                'contacto_emergencia_nombre.required' => 'El nombre del contacto de emergencia es requerido',
                'contacto_emergencia_parentesco.required' => 'El parentesco del contacto de emergencia es requerido',
                'contacto_emergencia_telefono.required' => 'El teléfono del contacto de emergencia es requerido',
            ]);

            $estudiante->update($validated);

            return redirect()->route('estudiantes.index')
                ->with('success', 'Estudiante actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Estudiante $estudiante)
    {
        try {
            $estudiante->delete();
            return redirect()->route('estudiantes.index')
                ->with('success', 'Estudiante eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el estudiante: ' . $e->getMessage());
        }
    }

    public function getHorarios(Estudiante $estudiante)
    {
        try {
            $diaActual = now('America/Caracas')->format('l');

            $dias = [
                'Monday' => 'Lunes',
                'Tuesday' => 'Martes',
                'Wednesday' => 'Miércoles',
                'Thursday' => 'Jueves',
                'Friday' => 'Viernes',
                'Saturday' => 'Sábado'
            ];

            $diaActualEsp = $dias[$diaActual] ?? null;
            if (!$diaActualEsp) {
                return response()->json(['error' => 'Día no válido'], 400);
            }

            if (!$estudiante->seccion_id) {
                return response()->json([], 400);
            }

            $horarios = Horario::whereHas('asignacion', function ($query) use ($estudiante) {
                $query->where('seccion_id', $estudiante->seccion_id);
            })
                ->where('dia', $diaActualEsp)
                ->with(['asignacion.materia', 'asignacion.seccion', 'asignacion.profesor.user'])
                ->get();

            $horariosArray = $horarios->map(function ($horario) {
                return [
                    'id' => $horario->id,
                    'asignacion_id' => $horario->asignacion_id,
                    'dia' => $horario->dia,
                    'hora_inicio' => $horario->hora_inicio,
                    'hora_fin' => $horario->hora_fin,
                    'aula' => $horario->aula,
                    'asignacion' => [
                        'id' => $horario->asignacion->id,
                        'materia' => [
                            'id' => $horario->asignacion->materia->id,
                            'nombre' => $horario->asignacion->materia->nombre
                        ],
                        'profesor' => [
                            'id' => $horario->asignacion->profesor->id ?? null,
                            'nombre' => $horario->asignacion->profesor->user->name ?? ($horario->asignacion->profesor->nombres ?? null)
                        ]
                    ]
                ];
            });

            return response()->json(['horarios' => $horariosArray]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los horarios'], 500);
        }
    }
}
