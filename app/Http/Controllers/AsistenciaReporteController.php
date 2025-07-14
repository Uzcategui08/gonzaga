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
            'seccion' => function($query) {
                $query->with('asignacion.seccion');
            },
            'grado' => function($query) {
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
        $user = auth()->user();
        
        if ($user->hasRole('coordinador')) {
            $seccionesCoordinador = $user->secciones->pluck('id');
            
            $asistencias = Asistencia::with([
                'profesor' => function($query) {
                    $query->with('user:id,name');
                },
                'materia' => function($query) {
                    $query->select('id', 'nombre');
                },
                'horario' => function($query) {
                    $query->with([
                        'asignacion' => function($query) {
                            $query->with('seccion');
                        }
                    ]);
                },
                'grado'
            ])
            ->whereHas('horario')
            ->whereHas('horario.asignacion', function($query) use ($seccionesCoordinador) {
                $query->whereIn('seccion_id', $seccionesCoordinador);
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
                'profesor' => function($query) {
                    $query->with('user:id,name');
                },
                'materia' => function($query) {
                    $query->select('id', 'nombre');
                }
            ])
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
            'asistencias' => $asistencias
        ]);
    }

    public function registro($id)
    {
        $asistencia = Asistencia::with([
            'profesor' => function($query) {
                $query->with('user:id,name');
            },
            'materia' => function($query) {
                $query->select('id', 'nombre');
            },
            'horario' => function($query) {
                $query->with([
                    'asignacion' => function($query) {
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
