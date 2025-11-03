<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\AsistenciaEstudiante;
use App\Models\Estudiante;
use App\Models\Profesor;
use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

    public function index()
    {
        $fecha = request('fecha', now()->format('Y-m-d'));
        $user = auth()->user();
        $flagOptions = [
            'falta_justificada' => 'Falta justificada',
            'tarea_pendiente' => 'Tarea pendiente',
            'conducta' => 'Problema de conducta',
            'pase_salida' => 'Pase de salida',
            'retraso' => 'Retraso',
            's_o' => 'S/O',
        ];

        $selectedFlag = request('flag');

        if (!array_key_exists($selectedFlag, $flagOptions)) {
            $selectedFlag = null;
        }

        if ($user->hasRole('coordinador')) {
            $seccionesCoordinador = $user->secciones->pluck('id');
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
                ->whereDate('fecha', $fecha)
                ->when($selectedFlag, function ($query) use ($selectedFlag) {
                    $query->where($selectedFlag, true);
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
            $asistencias = Asistencia::with([
                'profesor' => function ($query) {
                    $query->with('user:id,name');
                },
                'materia' => function ($query) {
                    $query->select('id', 'nombre');
                }
            ])
                ->whereDate('fecha', $fecha)
                ->when($selectedFlag, function ($query) use ($selectedFlag) {
                    $query->where($selectedFlag, true);
                })
                ->orderBy('fecha', 'desc')
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
        }

        return view('asistencias.reporte', [
            'asistencias' => $asistencias,
            'flagOptions' => $flagOptions,
            'selectedFlag' => $selectedFlag,
            'selectedDate' => $fecha,
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
                'estudiantes.apellidos'
            )
            ->get();

        $asistenciaData['estudiantes'] = $estudiantes->toArray();

        $pdf = Pdf::loadView('asistencias.registro-pdf', compact('asistenciaData'));
        return $pdf->stream('registro_asistencia_' . date('Y-m-d') . '.pdf');
    }
}
