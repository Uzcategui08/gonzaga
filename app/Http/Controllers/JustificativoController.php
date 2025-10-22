<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Justificativo;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use App\Models\Seccion;
use Illuminate\Support\Carbon;

class JustificativoController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();
        $filterDate = $this->resolveFilterDate($request->input('date'));

        $dateString = $filterDate->toDateString();

        if ($user->hasRole('coordinador')) {
            $secciones = $user->secciones;

            $justificativos = Justificativo::with(['estudiante', 'usuario'])
                ->whereHas('estudiante', function ($query) use ($secciones) {
                    $query->whereIn('seccion_id', $secciones->pluck('id'));
                })
                ->whereDate('fecha_inicio', $dateString)
                ->orderBy('fecha_inicio', 'desc')
                ->get();
        } else {
            $justificativos = Justificativo::with(['estudiante', 'usuario'])
                ->whereDate('fecha_inicio', $dateString)
                ->orderBy('fecha_inicio', 'desc')
                ->get();
        }

        return view('justificativos.index', [
            'justificativos' => $justificativos,
            'filterDate' => $filterDate,
        ]);
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

            if (!$profesor) {
                return redirect()->route('justificativos.profesor')
                    ->with('error', 'No tienes permisos para ver este justificativo');
            }

            $estudiante = Estudiante::where('id', $justificativo->estudiante_id)
                ->whereHas('seccion.asignaciones', function ($query) use ($profesor) {
                    $query->where('profesor_id', $profesor->id);
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

    public function indexProfesor(Request $request)
    {
        if (!auth()->user()->profesor) {
            return redirect('/dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        $profesor = auth()->user()->profesor;

        $filterDate = $this->resolveFilterDate($request->input('date'));
        $dateString = $filterDate->toDateString();

        $estudiantes = Estudiante::whereHas('seccion.asignaciones', function ($query) use ($profesor) {
            $query->where('profesor_id', $profesor->id);
        })->get();

        $justificativos = Justificativo::whereIn('estudiante_id', $estudiantes->pluck('id'))
            ->whereDate('fecha_inicio', $dateString)
            ->with(['estudiante', 'usuario'])
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return view('justificativos.profesor', [
            'justificativos' => $justificativos,
            'filterDate' => $filterDate,
        ]);
    }

    private function resolveFilterDate(?string $date): Carbon
    {
        try {
            if ($date) {
                return Carbon::createFromFormat('Y-m-d', $date, 'America/Caracas')->startOfDay();
            }
        } catch (\Throwable $exception) {
            // Ignore and fall back to today
        }

        return Carbon::now('America/Caracas')->startOfDay();
    }
}
