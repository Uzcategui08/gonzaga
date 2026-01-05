<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\AsistenciaEstudiante;
use App\Models\Estudiante;
use App\Models\Profesor;
use App\Models\Materia;
use App\Models\Seccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;


class AsistenciaReporteController extends Controller
{
    public function generatePdf()
    {
        $asistencias = Asistencia::with([
            'materia',
            'profesor',
            'seccion' => function ($query) {
                $query->with('asignacion.seccion');
            },
            'grado' => function ($query) {
                $query->select('id', 'nombre');
            }
        ])
            ->get();

        foreach ($asistencias as $asistencia) {
            $estudiantes = AsistenciaEstudiante::join('estudiantes', 'asistencia_estudiante.estudiante_id', '=', 'estudiantes.id')
                ->where('asistencia_id', $asistencia->id)
                ->select(
                    'asistencia_estudiante.id',
                    'asistencia_estudiante.estudiante_id',
                    'asistencia_estudiante.estado',
                    'asistencia_estudiante.observacion_individual',
                    'estudiantes.nombres',
                    'estudiantes.apellidos'
                )
                ->get();

            $asistencia->estudiantes = $estudiantes;
        }

        $pdf = Pdf::loadView('asistencias.reporte-pdf', compact('asistencias'));
        return $pdf->stream('reporte_asistencias_' . date('Y-m-d') . '.pdf');
    }

    public function index(Request $request)
    {
        [$startDate, $endDate] = $this->resolveDateRange(
            $request->input('fecha_desde', $request->input('fecha')),
            $request->input('fecha_hasta', $request->input('fecha')),
        );

        $startString = $startDate->toDateString();
        $endString = $endDate->toDateString();
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $flagOptions = [
            'falta_justificada' => 'Falta justificada',
            'tarea_pendiente' => 'Tarea pendiente',
            'conducta' => 'Problema de conducta',
            'pase_salida' => 'Pase de salida',
            'retraso' => 'Retraso',
            's_o' => 'S/O',
        ];

        $selectedFlag = request('flag');

        $selectedSeccionId = $request->input('seccion_id');
        if ($selectedSeccionId !== null && $selectedSeccionId !== '') {
            $selectedSeccionId = (int) $selectedSeccionId;
        } else {
            $selectedSeccionId = null;
        }

        if (!array_key_exists($selectedFlag, $flagOptions)) {
            $selectedFlag = null;
        }

        $seccionesOptions = [];

        if ($user && $user->hasRole('coordinador')) {
            $seccionesCoordinador = ($user->secciones ?? collect())->pluck('id');

            if ($seccionesCoordinador->isNotEmpty()) {
                $seccionesOptions = Seccion::with('grado:id,nombre')
                    ->whereIn('id', $seccionesCoordinador)
                    ->orderBy('nombre')
                    ->get()
                    ->sortBy(function ($seccion) {
                        return sprintf('%s_%s', optional($seccion->grado)->nombre ?? '', $seccion->nombre);
                    }, SORT_NATURAL | SORT_FLAG_CASE)
                    ->mapWithKeys(function ($seccion) {
                        $label = trim(sprintf('%s - %s', optional($seccion->grado)->nombre ?? 'Sin grado', $seccion->nombre));
                        return [$seccion->id => $label];
                    })
                    ->all();
            }

            if ($selectedSeccionId !== null && !$seccionesCoordinador->contains($selectedSeccionId)) {
                $selectedSeccionId = null;
            }

            $asistencias = Asistencia::with([
                'profesor' => function ($query) {
                    $query->with('user:id,name');
                },
                'materia' => function ($query) {
                    $query->select('id', 'nombre');
                },
                'horario' => function ($query) {
                    $query->with([
                        'asignacion' => function ($query) {
                            $query->with('seccion');
                        }
                    ]);
                },
                'grado'
            ])
                ->whereHas('horario')
                ->whereHas('horario.asignacion', function ($query) use ($seccionesCoordinador) {
                    $query->whereIn('seccion_id', $seccionesCoordinador);
                })
                ->whereBetween('fecha', [$startString, $endString])
                ->when($selectedSeccionId, function ($query) use ($selectedSeccionId) {
                    $query->whereHas('horario.asignacion', function ($query) use ($selectedSeccionId) {
                        $query->where('seccion_id', $selectedSeccionId);
                    });
                })
                ->when($selectedFlag, function ($query) use ($selectedFlag) {
                    $query->where($selectedFlag, true);
                })
                ->orderBy('fecha', 'desc')
                ->get();

            foreach ($asistencias as $asistencia) {
                $estudiantes = AsistenciaEstudiante::join('estudiantes', 'asistencia_estudiante.estudiante_id', '=', 'estudiantes.id')
                    ->where('asistencia_id', $asistencia->id)
                    ->whereIn('estudiantes.seccion_id', $seccionesCoordinador)
                    ->when($selectedSeccionId, function ($query) use ($selectedSeccionId) {
                        $query->where('estudiantes.seccion_id', $selectedSeccionId);
                    })
                    ->select(
                        'asistencia_estudiante.id',
                        'asistencia_estudiante.estudiante_id',
                        'asistencia_estudiante.estado',
                        'asistencia_estudiante.observacion_individual',
                        'estudiantes.nombres',
                        'estudiantes.apellidos'
                    )
                    ->get();
                $asistencia->estudiantes = $estudiantes;
            }
        } else {
            $seccionesOptions = Seccion::with('grado:id,nombre')
                ->orderBy('nombre')
                ->get()
                ->sortBy(function ($seccion) {
                    return sprintf('%s_%s', optional($seccion->grado)->nombre ?? '', $seccion->nombre);
                }, SORT_NATURAL | SORT_FLAG_CASE)
                ->mapWithKeys(function ($seccion) {
                    $label = trim(sprintf('%s - %s', optional($seccion->grado)->nombre ?? 'Sin grado', $seccion->nombre));
                    return [$seccion->id => $label];
                })
                ->all();

            $asistencias = Asistencia::with([
                'profesor' => function ($query) {
                    $query->with('user:id,name');
                },
                'materia' => function ($query) {
                    $query->select('id', 'nombre');
                },
                'horario' => function ($query) {
                    $query->with([
                        'asignacion' => function ($query) {
                            $query->with('seccion.grado');
                        }
                    ]);
                },
            ])
                ->whereBetween('fecha', [$startString, $endString])
                ->when($selectedSeccionId, function ($query) use ($selectedSeccionId) {
                    $query->whereHas('horario.asignacion', function ($query) use ($selectedSeccionId) {
                        $query->where('seccion_id', $selectedSeccionId);
                    });
                })
                ->when($selectedFlag, function ($query) use ($selectedFlag) {
                    $query->where($selectedFlag, true);
                })
                ->orderBy('fecha', 'desc')
                ->get();

            foreach ($asistencias as $asistencia) {
                $estudiantes = AsistenciaEstudiante::join('estudiantes', 'asistencia_estudiante.estudiante_id', '=', 'estudiantes.id')
                    ->where('asistencia_id', $asistencia->id)
                    ->when($selectedSeccionId, function ($query) use ($selectedSeccionId) {
                        $query->where('estudiantes.seccion_id', $selectedSeccionId);
                    })
                    ->select(
                        'asistencia_estudiante.id',
                        'asistencia_estudiante.estudiante_id',
                        'asistencia_estudiante.estado',
                        'asistencia_estudiante.observacion_individual',
                        'estudiantes.nombres',
                        'estudiantes.apellidos'
                    )
                    ->get();
                $asistencia->estudiantes = $estudiantes;
            }
        }

        return view('asistencias.reporte', [
            'asistencias' => $asistencias,
            'flagOptions' => $flagOptions,
            'selectedFlag' => $selectedFlag,
            'seccionesOptions' => $seccionesOptions,
            'selectedSeccionId' => $selectedSeccionId,
            'selectedStartDate' => $startString,
            'selectedEndDate' => $endString,
        ]);
    }

    public function registro($id)
    {
        $asistencia = Asistencia::with([
            'profesor' => function ($query) {
                $query->with('user:id,name');
            },
            'materia' => function ($query) {
                $query->select('id', 'nombre');
            },
            'horario' => function ($query) {
                $query->with([
                    'asignacion' => function ($query) {
                        $query->with('seccion');
                    }
                ]);
            }
        ])
            ->findOrFail($id);

        $asistenciaData = $asistencia->toArray();
        $asistenciaData['horario'] = $asistencia->horario->toArray();
        $asistenciaData['horario']['asignacion'] = $asistencia->horario->asignacion->toArray();
        $asistenciaData['horario']['asignacion']['seccion'] = $asistencia->horario->asignacion->seccion->toArray();

        $estudiantes = AsistenciaEstudiante::join('estudiantes', 'asistencia_estudiante.estudiante_id', '=', 'estudiantes.id')
            ->where('asistencia_id', $asistencia->id)
            ->select(
                'asistencia_estudiante.id',
                'asistencia_estudiante.estudiante_id',
                'asistencia_estudiante.estado',
                'asistencia_estudiante.observacion_individual',
                'estudiantes.nombres',
                'estudiantes.apellidos',
                'estudiantes.genero as genero'
            )
            ->get();

        $asistenciaData['estudiantes'] = $estudiantes->toArray();

        $pdf = Pdf::loadView('asistencias.registro-pdf', compact('asistenciaData'));
        return $pdf->stream('registro_asistencia_' . date('Y-m-d') . '.pdf');
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
