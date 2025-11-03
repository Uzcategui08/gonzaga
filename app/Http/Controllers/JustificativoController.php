<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Justificativo;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use App\Models\Seccion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class JustificativoController extends Controller
{

    public function index(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        [$startDate, $endDate] = $this->resolveDateRange(
            $request->input('start_date', $request->input('date')),
            $request->input('end_date', $request->input('date')),
        );

        $startString = $startDate->toDateString();
        $endString = $endDate->toDateString();

        if ($user && $user->hasRole('coordinador')) {
            $secciones = $user->secciones;

            $justificativos = Justificativo::with(['estudiante', 'usuario'])
                ->whereHas('estudiante', function ($query) use ($secciones) {
                    $query->whereIn('seccion_id', $secciones->pluck('id'));
                })
                ->whereDate('fecha_inicio', '<=', $endString)
                ->whereDate('fecha_fin', '>=', $startString)
                ->orderBy('fecha_inicio', 'desc')
                ->get();
        } else {
            $justificativos = Justificativo::with(['estudiante', 'usuario'])
                ->whereDate('fecha_inicio', '<=', $endString)
                ->whereDate('fecha_fin', '>=', $startString)
                ->orderBy('fecha_inicio', 'desc')
                ->get();
        }

        return view('justificativos.index', [
            'justificativos' => $justificativos,
            'filterStartDate' => $startDate,
            'filterEndDate' => $endDate,
        ]);
    }

    public function create(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $user->hasRole('coordinador')) {
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
            'user_id' => Auth::id(),
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
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $user->hasRole('profesor')) {
            $profesor = $user->profesor;

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
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user || !$user->profesor) {
            return redirect('/dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        $profesor = $user->profesor;

        [$startDate, $endDate] = $this->resolveDateRange(
            $request->input('start_date', $request->input('date')),
            $request->input('end_date', $request->input('date')),
        );

        $startString = $startDate->toDateString();
        $endString = $endDate->toDateString();

        $estudiantes = Estudiante::whereHas('seccion.asignaciones', function ($query) use ($profesor) {
            $query->where('profesor_id', $profesor->id);
        })->get();

        $justificativos = Justificativo::whereIn('estudiante_id', $estudiantes->pluck('id'))
            ->whereDate('fecha_inicio', '<=', $endString)
            ->whereDate('fecha_fin', '>=', $startString)
            ->with(['estudiante', 'usuario'])
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return view('justificativos.profesor', [
            'justificativos' => $justificativos,
            'filterStartDate' => $startDate,
            'filterEndDate' => $endDate,
        ]);
    }

    private function resolveDateRange(?string $startDate, ?string $endDate): array
    {
        $timezone = 'America/Caracas';

        $start = null;
        $end = null;

        try {
            if ($startDate) {
                $start = Carbon::createFromFormat('Y-m-d', $startDate, $timezone)->startOfDay();
            }
        } catch (\Throwable $exception) {
            $start = null;
        }

        try {
            if ($endDate) {
                $end = Carbon::createFromFormat('Y-m-d', $endDate, $timezone)->endOfDay();
            }
        } catch (\Throwable $exception) {
            $end = null;
        }

        if (!$start && !$end) {
            $now = Carbon::now($timezone);
            return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
        }

        if (!$start) {
            $start = $end?->copy()->startOfDay();
        }

        if (!$end) {
            $end = $start?->copy()->endOfDay();
        }

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start->startOfDay(), $end->endOfDay()];
    }
}
