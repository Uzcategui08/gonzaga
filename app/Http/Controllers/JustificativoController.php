<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Justificativo;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use App\Models\Seccion;

class JustificativoController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        
        if ($user->hasRole('coordinador')) {
            $secciones = $user->secciones;

            $justificativos = Justificativo::with(['estudiante', 'usuario'])
                ->whereHas('estudiante', function($query) use ($secciones) {
                    $query->whereIn('seccion_id', $secciones->pluck('id'));
                })
                ->orderBy('fecha_inicio', 'desc')
                ->get();
        } else {
            $justificativos = Justificativo::with(['estudiante', 'usuario'])
                ->orderBy('fecha_inicio', 'desc')
                ->get();
        }

        return view('justificativos.index', compact('justificativos'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        
        if ($user->hasRole('coordinador')) {
            $secciones = $user->secciones;
            $estudiantes = Estudiante::whereIn('seccion_id', $secciones->pluck('id'))
                ->get();
        } else {
            $secciones = Seccion::all();
            $estudiantes = Estudiante::all();
        }

        return view('justificativos.create-general', compact('estudiantes', 'secciones'));
    }

    public function createSpecific($estudiante_id)
    {
        if (!$estudiante_id) {
            return redirect()->back()->with('error', 'Debe seleccionar un estudiante');
        }

        $estudiante = Estudiante::findOrFail($estudiante_id);
        return view('justificativos.create', compact('estudiante'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:fecha_inicio',
            'motivo' => 'required|string',
            'observaciones' => 'nullable|string',
            'tipo' => 'required|in:salud,familiar,otro',
            'aprobado' => 'nullable|boolean'
        ]);

        $justificativo = Justificativo::create([
            'estudiante_id' => $validated['estudiante_id'],
            'user_id' => auth()->id(),
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'motivo' => $validated['motivo'],
            'observaciones' => $validated['observaciones'],
            'tipo' => $validated['tipo'],
            'aprobado' => $request->has('aprobado') ? (bool)$validated['aprobado'] : false
        ]);

        return redirect()->route('justificativos.index')
            ->with('success', 'Justificativo creado exitosamente');
    }

    public function show(Justificativo $justificativo)
    {
        if (auth()->user()->hasRole('profesor')) {
            $profesor = auth()->user()->profesor;
            $materias = $profesor->materias;

            $estudiante = Estudiante::where('id', $justificativo->estudiante_id)
                ->whereHas('seccion.asignaciones.materia', function($query) use ($materias) {
                    $query->whereIn('materias.id', $materias->pluck('id'));
                })
                ->first();
            
            if (!$estudiante) {
                return redirect()->route('justificativos.profesor')
                    ->with('error', 'No tienes permisos para ver este justificativo');
            }
        }

        return view('justificativos.show', compact('justificativo'));
    }

    public function edit(Justificativo $justificativo)
    {
        return view('justificativos.edit', compact('justificativo'));
    }

    public function update(Request $request, Justificativo $justificativo)
    {
        $validated = $request->validate([
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:fecha_inicio',
            'motivo' => 'required|string',
            'observaciones' => 'nullable|string',
            'tipo' => 'required|in:salud,familiar,otro',
            'aprobado' => 'nullable|boolean'
        ]);

        $justificativo->fecha_inicio = $validated['fecha_inicio'];
        $justificativo->fecha_fin = $validated['fecha_fin'];
        $justificativo->motivo = $validated['motivo'];
        $justificativo->observaciones = $validated['observaciones'];
        $justificativo->tipo = $validated['tipo'];
        $justificativo->aprobado = $request->input('aprobado', false);
        $justificativo->save();

        return redirect()->route('justificativos.index')
            ->with('success', 'Justificativo actualizado exitosamente');
    }

    public function destroy(Justificativo $justificativo)
    {
        $justificativo->delete();
        return redirect()->route('justificativos.index')
            ->with('success', 'Justificativo eliminado exitosamente');
    }

    public function indexProfesor()
    {
        if (!auth()->user()->profesor) {
            return redirect('/dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        $profesor = auth()->user()->profesor;
        $materias = $profesor->materias;

        $estudiantes = Estudiante::whereHas('seccion.asignaciones.materia', function($query) use ($materias) {
            $query->whereIn('materias.id', $materias->pluck('id'));
        })->get();

        $justificativos = Justificativo::whereIn('estudiante_id', $estudiantes->pluck('id'))
            ->with(['estudiante', 'usuario'])
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return view('justificativos.profesor', compact('justificativos'));
    }

}
