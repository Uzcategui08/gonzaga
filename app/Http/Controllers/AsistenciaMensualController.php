<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\AsistenciaEstudiante;
use App\Models\Estudiante;
use App\Models\Materia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Profesor;
use App\Models\Seccion;
use App\Models\Asignacion;
use Illuminate\Support\Facades\Log;

class AsistenciaMensualController extends Controller
{
    public function index(Request $request)
    {
        $secciones = Seccion::all();

        $meses = [
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre'
        ];

        return view('asistencias.mensual', [
            'secciones' => $secciones,
            'meses' => $meses,
            'selectedSeccion' => $request->seccion_id
        ]);
    }

    public function generatePdf(Request $request)
    {
        try {
            $request->validate([
                'seccion_id' => 'required|exists:secciones,id',
                'periodo' => 'required|in:diario,mensual',
                'fecha' => 'required_if:periodo,diario|date',
                'mes' => 'required_if:periodo,mensual|numeric|min:1|max:12',
            ]);

            $seccion = Seccion::with(['estudiantes'])->findOrFail($request->seccion_id);

            $asistencias = Asistencia::with(['asistencia_estudiante' => function($query) use ($seccion) {
                $query->whereIn('estudiante_id', $seccion->estudiantes->pluck('id'));
            }])
            ->when($request->periodo === 'diario' && $request->fecha, function($query) use ($request) {
                $query->whereDate('fecha', $request->fecha);
            })
            ->when($request->periodo === 'mensual', function($query) use ($request) {
                $query->whereRaw('EXTRACT(MONTH FROM fecha) = ?', [(int)$request->mes]);
            })
            ->get();

            Log::info('Attendance records:', [
                'count' => $asistencias->count(),
                'first_record' => $asistencias->first() ? $asistencias->first()->toArray() : 'No records'
            ]);

            $totalStudents = $seccion->estudiantes->count();
            $totalClasses = $asistencias->count();
            $absences = [];
            $pases = [];
            $presentes = [];
            $attendanceRate = 0;

            foreach ($seccion->estudiantes as $student) {
                $absences[$student->id] = 0;
                $pases[$student->id] = 0;
                $presentes[$student->id] = 0;
            }

            if ($asistencias && $asistencias->isNotEmpty()) {
                foreach ($asistencias as $asistencia) {
                    foreach ($asistencia->asistencia_estudiante as $asistenciaEstudiante) {
                        $studentId = $asistenciaEstudiante->estudiante_id;

                        switch ($asistenciaEstudiante->estado) {
                            case 'A': 
                                $presentes[$studentId]++;
                                break;
                            case 'P': 
                                $pases[$studentId]++;
                                break;
                            case 'I': 
                                $absences[$studentId]++;
                                break;
                        }
                    }
                }
            }

            if ($totalStudents > 0 && $totalClasses > 0) {
                $attendanceRate = (array_sum($presentes) / ($totalStudents * $totalClasses)) * 100;
            }

            $students = $seccion->estudiantes->sortBy(function($student) {
                return $student->nombres . ' ' . $student->apellidos;
            });

            date_default_timezone_set('America/Caracas');
            
            $pdf = Pdf::loadView('asistencias.mensual-pdf', [
                'seccion' => $seccion,
                'students' => $students,
                'asistencias' => $asistencias,
                'totalStudents' => $totalStudents,
                'totalClasses' => $totalClasses,
                'absences' => $absences,
                'pases' => $pases,
                'presentes' => $presentes,
                'attendanceRate' => $attendanceRate,
                'periodo' => $request->periodo,
                'fecha' => $request->fecha,
                'mes' => $request->mes,
                'meses' => [
                    '01' => 'Enero',
                    '02' => 'Febrero',
                    '03' => 'Marzo',
                    '04' => 'Abril',
                    '05' => 'Mayo',
                    '06' => 'Junio',
                    '07' => 'Julio',
                    '08' => 'Agosto',
                    '09' => 'Septiembre',
                    '10' => 'Octubre',
                    '11' => 'Noviembre',
                    '12' => 'Diciembre'
                ]
            ]);

            date_default_timezone_set('UTC');
            
            return $pdf->stream('asistencia-mensual.pdf');
        } catch (\Exception $e) {
            Log::error('Error generating monthly attendance PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }
    
}
