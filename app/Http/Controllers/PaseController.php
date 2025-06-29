<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pase;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PaseController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        $user = auth()->user();
        
        if ($user->hasRole('coordinador')) {
            $secciones = $user->secciones;
            
            $pases = Pase::with(['estudiante', 'usuario'])
                ->whereHas('estudiante', function($query) use ($secciones) {
                    $query->whereIn('seccion_id', $secciones->pluck('id'));
                })
                ->orderBy('fecha', 'desc')
                ->get();
        } else {
            $pases = Pase::with(['estudiante', 'usuario'])
                ->orderBy('fecha', 'desc')
                ->get();
        }

        return view('pases.index', compact('pases'));
    }

    public function create()
    {
        $user = auth()->user();
        
        if ($user->hasRole('coordinador')) {
            $secciones = $user->secciones;
            
            $estudiantes = Estudiante::whereIn('seccion_id', $secciones->pluck('id'))
                ->with('seccion')
                ->get();
        } else {
            $estudiantes = Estudiante::with('seccion')
                ->get();
        }

        return view('pases.create', compact('estudiantes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'horario_id' => 'required|exists:horarios,id',
            'motivo' => 'required|string',
            'observaciones' => 'nullable|string',
            'fecha' => 'required|date',
            'hora_llegada' => 'required|date_format:H:i',
            'aprobado' => 'boolean'
        ]);

        $hora_llegada = strtotime($validated['hora_llegada']);
        $hora_inicio_clases = strtotime(config('app.hora_inicio_clases'));
        
        if ($hora_llegada <= $hora_inicio_clases) {
            return redirect()->back()->withErrors([
                'hora_llegada' => 'La hora de llegada debe ser después de la hora de inicio de clases'
            ])->withInput();
        }

        $horario = \App\Models\Horario::with(['asignacion.materia', 'asignacion.seccion'])->find($validated['horario_id']);
        
        if (!$horario) {
            return redirect()->back()->withErrors([
                'horario_id' => 'Horario no encontrado'
            ])->withInput();
        }

        $pase = Pase::create([
            'estudiante_id' => $validated['estudiante_id'],
            'horario_id' => $horario->id,
            'motivo' => $validated['motivo'],
            'observaciones' => $validated['observaciones'],
            'fecha' => $validated['fecha'],
            'hora_llegada' => $validated['hora_llegada'],
            'aprobado' => $validated['aprobado'] ?? false,
            'user_id' => Auth::id()
        ]);

        $profesor = $horario->asignacion->profesor;
        
        if ($profesor) {
            $materia = $pase->horario?->asignacion?->materia?->nombre ?? 'Desconocida';
            $seccion = $pase->horario?->asignacion?->seccion?->nombre ?? 'Desconocida';
            
            $profesor->notify(
                new \App\Notifications\PaseAsignadoNotification(
                    $pase,
                    $pase->estudiante->nombres . ' ' . $pase->estudiante->apellidos,
                    $pase->motivo,
                    $pase->hora_llegada,
                    $materia,
                    $seccion
                )
            );
        }

        return redirect()->route('pases.index')->with('success', 'Pase creado exitosamente.');
    }

    public function show(Pase $pase)
    {
        return view('pases.show', compact('pase'));
    }

    public function edit(Pase $pase)
    {
        return view('pases.edit', compact('pase'));
    }

    public function update(Request $request, Pase $pase)
    {
        $validated = $request->validate([
            'motivo' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'fecha' => 'nullable|date',
            'hora_llegada' => 'nullable|date_format:H:i',
            'aprobado' => 'nullable|boolean'
        ]);

        if (isset($validated['hora_llegada'])) {
            $hora_llegada = strtotime($validated['hora_llegada']);
            $hora_inicio_clases = strtotime(config('app.hora_inicio_clases'));
            
            if ($hora_llegada <= $hora_inicio_clases) {
                return redirect()->back()->withErrors([
                    'hora_llegada' => 'La hora de llegada debe ser después de la hora de inicio de clases'
                ])->withInput();
            }
        }

        $pase->update([
            'motivo' => $validated['motivo'] ?? $pase->motivo,
            'observaciones' => $validated['observaciones'] ?? $pase->observaciones,
            'fecha' => $validated['fecha'] ?? $pase->fecha,
            'hora_llegada' => $validated['hora_llegada'] ?? $pase->hora_llegada,
            'aprobado' => $request->has('aprobado') ? (bool)$validated['aprobado'] : $pase->aprobado
        ]);

        return redirect()->route('pases.index')
            ->with('success', 'Pase actualizado exitosamente');
    }

    public function destroy(Pase $pase)
    {
        $pase->delete();
        return redirect()->route('pases.index')
            ->with('success', 'Pase eliminado exitosamente');
    }
}
