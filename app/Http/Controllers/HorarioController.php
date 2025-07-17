<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Asignacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Seccion;

class HorarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $professorId = $request->query('professor_id');
            $sectionId = $request->query('section_id');
            
            $query = Horario::with([
                'asignacion.profesor.user', 
                'asignacion.materia', 
                'asignacion.seccion', 
                'grado.seccion.grado'
            ]);

            if ($user->hasRole('coordinador')) {
                $seccionesCoordinador = $user->secciones->pluck('id');
                $query->whereHas('asignacion', function($query) use ($seccionesCoordinador) {
                    $query->whereIn('seccion_id', $seccionesCoordinador);
                });
            }

            if ($professorId) {
                $query->whereHas('asignacion', function($query) use ($professorId) {
                    $query->where('profesor_id', $professorId);
                });
            } elseif ($sectionId) {
                $query->whereHas('asignacion', function($query) use ($sectionId) {
                    $query->where('seccion_id', $sectionId);
                });
            }

            $horarios = $query
                ->orderBy('dia')
                ->orderBy('hora_inicio')
                ->get();

            $professors = Profesor::with('user')->get();
            $sections = \App\Models\Seccion::with('grado')->get();

            return view('horarios.index', compact('horarios', 'professors', 'sections'));
        } catch (\Exception $e) {
            return view('horarios.index')->with('error', 'Error al cargar los horarios: ' . $e->getMessage());
        }
    }

    /**
     * Show the authenticated professor's schedule.
     */
    public function horarioProfesor()
    {
        try {
            if (!auth()->check()) {
                return redirect()->route('login')->with('error', 'Por favor, inicie sesión primero');
            }
            $profesor = auth()->user()->profesor;
            if (!$profesor) {
                return view('horarios.profesor')->with('error', 'No tienes un perfil de profesor asociado');
            }

            $horarios = Horario::with(['asignacion.materia'])
                ->whereHas('asignacion', function($query) use ($profesor) {
                    $query->where('profesor_id', $profesor->id);
                })
                ->orderBy('dia')
                ->orderBy('hora_inicio')
                ->get();

            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

            return view('horarios.profesor', compact('horarios', 'dias'));
        } catch (\Exception $e) {
            return view('horarios.profesor')->with('error', 'Error al cargar su horario. Por favor, contacte al administrador.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $asignaciones = Asignacion::with(['profesor.user', 'materia', 'seccion'])
            ->get();

        if ($asignaciones->isEmpty()) {
            return redirect()->route('horarios.index')
                ->with('error', 'No hay asignaciones disponibles para crear horarios.');
        }

        $asignaciones = $asignaciones->sortBy(function ($asignacion) {
            return $asignacion->materia->nombre;
        });

        return view('horarios.create', compact('asignaciones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $asignacion = Asignacion::find($request->asignacion_id);
            if (!$asignacion) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'La asignación seleccionada no existe.');
            }

            $validated = $request->validate([
                'asignacion_id' => 'required|exists:asignaciones,id',
                'dia' => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                'aula' => 'required|max:50',
            ], [
                'asignacion_id.required' => 'La asignación es requerida',
                'dia.required' => 'El día es requerido',
                'hora_inicio.required' => 'La hora de inicio es requerida',
                'hora_fin.required' => 'La hora de fin es requerida',
                'hora_fin.after' => 'La hora de fin debe ser después de la hora de inicio',
                'aula.required' => 'El aula es requerida',
            ]);

            $exists = Horario::where('asignacion_id', $request->asignacion_id)
                ->where('dia', $request->dia)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('hora_inicio', [$request->hora_inicio, $request->hora_fin])
                        ->orWhereBetween('hora_fin', [$request->hora_inicio, $request->hora_fin]);
                })
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ya existe un horario para esta asignación en el mismo día y hora.');
            }

            $horario = Horario::create($validated);

            return redirect()->route('horarios.index')
                ->with('success', 'Horario creado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el horario: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Horario $horario)
    {
        return view('horarios.show', compact('horario'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Horario $horario)
    {
        $horario->load('asignacion.profesor.user', 'asignacion.materia', 'asignacion.seccion');
        
        $asignaciones = Asignacion::with(['profesor.user', 'materia', 'seccion'])
            ->get()
            ->sortBy(function ($asignacion) {
                return $asignacion->materia->nombre;
            });
        
        return view('horarios.edit', compact('horario', 'asignaciones'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Horario $horario)
    {
        try {
            $validated = $request->validate([
                'asignacion_id' => 'required|exists:asignaciones,id',
                'dia' => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado',
                'hora_inicio' => 'required',
                'hora_fin' => 'required|after:hora_inicio',
                'aula' => 'required|max:50',
            ], [
                'asignacion_id.required' => 'La asignación es requerida',
                'dia.required' => 'El día es requerido',
                'hora_inicio.required' => 'La hora de inicio es requerida',
                'hora_fin.required' => 'La hora de fin es requerida',
                'hora_fin.after' => 'La hora de fin debe ser después de la hora de inicio',
                'aula.required' => 'El aula es requerida',
            ]);

            $validated['hora_inicio'] = \Carbon\Carbon::parse($validated['hora_inicio'])->format('H:i');
            $validated['hora_fin'] = \Carbon\Carbon::parse($validated['hora_fin'])->format('H:i');

            $exists = Horario::where('asignacion_id', $request->asignacion_id)
                ->where('dia', $request->dia)
                ->where('id', '!=', $horario->id)
                ->where(function ($query) use ($validated) {
                    $query->whereBetween('hora_inicio', [$validated['hora_inicio'], $validated['hora_fin']])
                        ->orWhereBetween('hora_fin', [$validated['hora_inicio'], $validated['hora_fin']]);
                })
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ya existe un horario para esta asignación en el mismo día y hora.');
            }

            $horario->update($validated);

            return redirect()->route('horarios.index')
                ->with('success', 'Horario actualizado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el horario: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Horario $horario)
    {
        try {
            $horario->delete();
            return redirect()->route('horarios.index')
                ->with('success', 'Horario eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el horario: ' . $e->getMessage());
        }
    }

    public function horarioProfesorAdmin(Request $request)
    {
        try {
            $user = auth()->user();
            if ($user->hasRole('admin')) {
                $professors = Profesor::with(['user', 'secciones'])
                    ->join('users', 'profesores.user_id', '=', 'users.id')
                    ->orderBy('users.name')
                    ->get();
            } else {
                $seccionesCoordinador = $user->secciones->pluck('id');
                Log::info('Secciones del coordinador:', ['secciones' => $seccionesCoordinador->toArray()]);

                $professors = Profesor::with(['user', 'secciones'])
                    ->whereHas('secciones', function($query) use ($seccionesCoordinador) {
                        $query->whereIn('seccion_id', $seccionesCoordinador);
                    })
                    ->join('users', 'profesores.user_id', '=', 'users.id')
                    ->orderBy('users.name')
                    ->get();
            }
            Log::info('Profesores encontrados:', ['count' => $professors->count()]);

            $selectedProfessor = null;
            $selectedSection = null;
            $horarios = null; // Inicialmente null para indicar que no hay selección
            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

            // Obtener secciones disponibles
            $sections = Seccion::with('grado')->get();

            // Inicializar la consulta base
            $query = Horario::with(['asignacion.materia', 'asignacion.seccion.grado']);

            // Si no hay selección, no ejecutar la consulta
            if (!$request->has('professor_id') && !$request->has('section_id')) {
                return view('horarios.horario', compact('professors', 'sections', 'dias', 'horarios', 'selectedProfessor', 'selectedSection'));
            }

            // Si hay filtro de profesor
            if ($request->has('professor_id')) {
                try {
                    $selectedProfessor = Profesor::with('user')
                        ->whereHas('user', function($query) use ($request) {
                            $query->where('id', $request->professor_id);
                        })->first();
                    
                    if (!$selectedProfessor) {
                        return redirect()->back()->with('error', 'Profesor no encontrado.');
                    }

                    // Filtrar por profesor
                    $query->whereHas('asignacion', function($query) use ($selectedProfessor) {
                        $query->where('profesor_id', $selectedProfessor->id);
                    });

                    // Aplicar restricciones de coordinador si es necesario
                    if (!$user->hasRole('admin')) {
                        $query->whereHas('asignacion', function($query) use ($seccionesCoordinador) {
                            $query->whereIn('seccion_id', $seccionesCoordinador);
                        });
                    }

                } catch (\Exception $e) {
                    Log::error('Error buscando profesor:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    return redirect()->back()
                        ->with('error', 'Error al buscar el profesor seleccionado.');
                }
            }
            // Si hay filtro de sección
            else if ($request->has('section_id')) {
                try {
                    $selectedSection = Seccion::with('grado')->find($request->section_id);
                    if (!$selectedSection) {
                        return redirect()->back()->with('error', 'Sección no encontrada.');
                    }

                    // Filtrar por sección
                    $query->whereHas('asignacion', function($query) use ($selectedSection) {
                        $query->where('seccion_id', $selectedSection->id);
                    });

                    // Aplicar restricciones de coordinador si es necesario
                    if (!$user->hasRole('admin')) {
                        $query->whereHas('asignacion', function($query) use ($seccionesCoordinador) {
                            $query->whereIn('seccion_id', $seccionesCoordinador);
                        });
                    }

                } catch (\Exception $e) {
                    Log::error('Error buscando sección:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    return redirect()->back()
                        ->with('error', 'Error al buscar la sección seleccionada.');
                }
            }
            // Si no hay ningún filtro
            else {
                // Aplicar restricciones de coordinador si es necesario
                if (!$user->hasRole('admin')) {
                    $query->whereHas('asignacion', function($query) use ($seccionesCoordinador) {
                        $query->whereIn('seccion_id', $seccionesCoordinador);
                    });
                }
            }

            // Ejecutar la consulta final
            $horarios = $query
                ->orderBy('dia')
                ->orderBy('hora_inicio')
                ->get();
            Log::info('Horarios encontrados:', ['count' => $horarios->count()]);

            return view('horarios.horario', compact(
                'professors',
                'selectedProfessor',
                'horarios',
                'dias',
                'sections',
                'selectedSection'
            ));
        } catch (\Exception $e) {
            Log::error('Error en horarioProfesorAdmin:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->with('error', 'Error al cargar el horario. Por favor, contacte al administrador.');
        }
    }

}