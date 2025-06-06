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
        $asistencias = Asistencia::with(['materia', 'profesor'])
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

        return view('asistencias.reporte', [
            'asistencias' => $asistencias
        ]);
    }
}
