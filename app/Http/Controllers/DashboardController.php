<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Horario;
use App\Models\Asistencia;
use App\Models\Asignacion;
use App\Models\AsistenciaEstudiante;
use App\Models\Materia;
use App\Models\Seccion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'totalEstudiantes' => 0,
            'totalProfesores' => 0,
            'totalSecciones' => 0,
            'totalMaterias' => 0,
            'tardiosHoy' => 0,
            'inasistenciasHoy' => 0,
            'notifications' => collect(),
            'secciones' => collect(),
            'horarioHoy' => collect(),
            'asistenciasHoy' => collect(),
            'totalClases' => 0,
            'clasesConAsistencia' => 0,
            'porcentajeAsistencia' => 0,
            'estudiantesConMasFaltas' => collect(),
            'proximasClases' => collect(),
            'fechaActual' => Carbon::now('America/Caracas'),
            'asistenciasPorMateria' => collect(),
            'asistenciaPorEstado' => collect(),
            'estadosAsistencia' => collect(),
            'estudiantesPorSeccion' => collect(),
            'profesoresPorMateria' => collect(),
            'attendanceTrend' => collect(),
            'promedioAsistencia' => 0,
            'totalEstudiantesProfesor' => 0,
            'inasistenciasProfesor' => 0
        ];

        $user = auth()->user();

        if ($user->profesor) {
            $data['notifications'] = $user->profesor->notifications()->latest()->get();

            $fechaActual = now('America/Caracas');

            $data['totalEstudiantesProfesor'] = \App\Models\AsistenciaEstudiante::whereHas('asistencia')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->where('asignaciones.profesor_id', $user->profesor->id)
                ->whereDate('asistencias.fecha', $fechaActual)
                ->distinct('estudiante_id')
                ->count('estudiante_id');

            $data['inasistenciasProfesor'] = \App\Models\AsistenciaEstudiante::where('estado', 'I')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->where('asignaciones.profesor_id', $user->profesor->id)
                ->whereDate('asistencias.fecha', $fechaActual)
                ->distinct('estudiante_id')
                ->count('estudiante_id');
        }

        if ($user->hasRole('admin')) {
            $data['totalEstudiantes'] = \App\Models\Estudiante::count();
            $data['totalProfesores'] = \App\Models\Profesor::count();
            $fechaActual = now('America/Caracas');
            $diaActual = $fechaActual->format('l');

            $asistenciasQuery = \App\Models\AsistenciaEstudiante::where('asistencia_estudiante.estado', 'A')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->join('secciones', 'asignaciones.seccion_id', '=', 'secciones.id')
                ->join('estudiantes', 'secciones.id', '=', 'estudiantes.seccion_id')
                ->whereDate('asistencias.fecha', $fechaActual);

            $data['asistenciasHoy'] = $asistenciasQuery->distinct('estudiante_id')->count('estudiante_id');

            $data['inasistenciasHoy'] = \App\Models\AsistenciaEstudiante::where('asistencia_estudiante.estado', 'I')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->whereDate('asistencias.fecha', $fechaActual)
                ->distinct('estudiante_id')
                ->count('estudiante_id');

            $data['totalClasesHoy'] = \App\Models\Horario::whereHas('asistencia', function ($query) use ($fechaActual) {
                $query->whereDate('fecha', $fechaActual);
            })->count();

            $attendanceByDay = \DB::table('asistencia_estudiante')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->join('secciones', 'asignaciones.seccion_id', '=', 'secciones.id')
                ->join('estudiantes', 'secciones.id', '=', 'estudiantes.seccion_id')
                ->where('asistencia_estudiante.estado', 'A')
                ->whereBetween('asistencias.fecha', [Carbon::now('America/Caracas')->subDays(30), Carbon::now('America/Caracas')])
                ->when($user->hasRole('coordinador'), function ($query) use ($user) {
                    $query->whereIn('secciones.id', $user->secciones->pluck('id'));
                })
                ->select(
                    'horarios.dia',
                    \DB::raw('count(*) as total_asistencias'),
                    \DB::raw('count(DISTINCT asistencia_estudiante.estudiante_id) as estudiantes_unicos')
                )
                ->groupBy('horarios.dia')
                ->get()
                ->map(function ($item) use ($user) {
                    $totalStudents = $user->hasRole('coordinador') ?
                        \App\Models\Estudiante::whereIn('seccion_id', $user->secciones->pluck('id'))->count() :
                        \App\Models\Estudiante::count();
                    $attendanceRate = ($totalStudents > 0) ? round(($item->estudiantes_unicos / $totalStudents) * 100) : 0;
                    return [
                        'dia' => $item->dia,
                        'tasa' => $attendanceRate
                    ];
                });

            $data['attendanceByDay'] = $attendanceByDay;

            $data['tardiosHoy'] = \App\Models\AsistenciaEstudiante::where('asistencia_estudiante.estado', 'P')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->whereDate('asistencias.fecha', $fechaActual)
                ->distinct('estudiante_id')
                ->count('estudiante_id');

            $last30Days = Carbon::now('America/Caracas')->subDays(30);
            $totalStudents = \App\Models\Estudiante::count();

            $totalAttendances = \App\Models\AsistenciaEstudiante::where('estado', 'A')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->whereBetween('asistencias.fecha', [$last30Days, $fechaActual])
                ->distinct('asistencia_estudiante.estudiante_id')
                ->count('asistencia_estudiante.estudiante_id');

            $attendancePercentage = ($totalStudents > 0) ?
                round(($totalAttendances / $totalStudents) * 100) : 0;
            $data['promedioAsistencia'] = $attendancePercentage;

            $periods = \App\Models\Horario::select('dia', \DB::raw('count(*) as total'))
                ->whereHas('asistencia', function ($query) use ($fechaActual) {
                    $query->whereDate('fecha', $fechaActual);
                })
                ->groupBy('dia')
                ->orderBy('total', 'desc')
                ->first();
            $data['periodoMasActivo'] = $periods ? $periods->dia : 'N/A';
            $data['totalClasesPeriodo'] = $periods ? $periods->total : 0;

            $topClass = \App\Models\AsistenciaEstudiante::select('secciones.nombre as clase', \DB::raw('count(*) as total'))
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->join('secciones', 'asignaciones.seccion_id', '=', 'secciones.id')
                ->where('estado', 'A')
                ->whereDate('asistencias.fecha', $fechaActual)
                ->groupBy('secciones.nombre')
                ->orderBy('total', 'desc')
                ->first();
            $data['claseTop'] = $topClass ? $topClass->clase : 'N/A';
            $data['asistenciaClaseTop'] = $topClass ? $topClass->total : 0;

            $topSubject = \App\Models\AsistenciaEstudiante::select('materias.nombre as materia', \DB::raw('count(*) as total'))
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->join('materias', 'asignaciones.materia_id', '=', 'materias.id')
                ->where('estado', 'A')
                ->whereDate('asistencias.fecha', $fechaActual)
                ->groupBy('materias.nombre')
                ->orderBy('total', 'desc')
                ->first();
            $data['materiaTop'] = $topSubject ? $topSubject->materia : 'N/A';
            $data['asistenciaMateriaTop'] = $topSubject ? $topSubject->total : 0;
        } elseif ($user->hasRole('coordinador')) {
            $seccionesCoordinador = $user->secciones->pluck('id');

            $data['totalEstudiantes'] = \App\Models\Estudiante::whereIn('seccion_id', $seccionesCoordinador)->count();
            $data['totalProfesores'] = \App\Models\Profesor::whereHas('secciones', function ($query) use ($seccionesCoordinador) {
                $query->whereIn('secciones.id', $seccionesCoordinador);
            })->distinct()->count();

            $fechaActual = now('America/Caracas');
            $diaActual = $fechaActual->format('l');

            $asistenciasQuery = \App\Models\AsistenciaEstudiante::where('asistencia_estudiante.estado', 'A')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->join('secciones', 'asignaciones.seccion_id', '=', 'secciones.id')
                ->join('estudiantes', 'secciones.id', '=', 'estudiantes.seccion_id')
                ->whereIn('secciones.id', $seccionesCoordinador)
                ->whereDate('asistencias.fecha', $fechaActual);

            $data['asistenciasHoy'] = $asistenciasQuery->distinct('estudiante_id')->count('estudiante_id');

            $data['inasistenciasHoy'] = \App\Models\AsistenciaEstudiante::where('asistencia_estudiante.estado', 'I')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->whereIn('asignaciones.seccion_id', $seccionesCoordinador)
                ->whereDate('asistencias.fecha', $fechaActual)
                ->distinct('estudiante_id')
                ->count('estudiante_id');

            $data['tardiosHoy'] = \App\Models\AsistenciaEstudiante::where('asistencia_estudiante.estado', 'P')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->whereIn('asignaciones.seccion_id', $seccionesCoordinador)
                ->whereDate('asistencias.fecha', $fechaActual)
                ->distinct('estudiante_id')
                ->count('estudiante_id');

            $last30Days = Carbon::now('America/Caracas')->subDays(30);

            Log::info('Attendance date range:', [
                'start' => $last30Days->format('Y-m-d'),
                'end' => $fechaActual->format('Y-m-d')
            ]);

            $totalStudents = \App\Models\Estudiante::whereIn('seccion_id', $seccionesCoordinador)->count();
            Log::info('Total students:', ['count' => $totalStudents]);

            $studentsWithAttendance = \App\Models\AsistenciaEstudiante::where('estado', 'A')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->whereBetween('asistencias.fecha', [$last30Days, $fechaActual])
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->whereIn('asignaciones.seccion_id', $seccionesCoordinador)
                ->distinct('estudiante_id')
                ->count('estudiante_id');
            Log::info('Students with attendance:', ['count' => $studentsWithAttendance]);

            $totalAttendances = \App\Models\AsistenciaEstudiante::where('estado', 'A')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->whereBetween('asistencias.fecha', [$last30Days, $fechaActual])
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->whereIn('asignaciones.seccion_id', $seccionesCoordinador)
                ->count();
            Log::info('Total attendances:', ['count' => $totalAttendances]);

            Log::info('Calculating promedioAsistencia:', ['totalStudents' => $totalStudents, 'totalAttendances' => $totalAttendances]);
            $attendancePercentage = ($studentsWithAttendance > 0) ?
                round(($totalAttendances / ($studentsWithAttendance * 30)) * 100) : 0;
            Log::info('Attendance calculation:', [
                'students_with_attendance' => $studentsWithAttendance,
                'total_attendances' => $totalAttendances,
                'percentage' => $attendancePercentage
            ]);
            Log::info('Setting promedioAsistencia:', ['value' => $attendancePercentage]);
            $data['promedioAsistencia'] = $attendancePercentage;

            $totalClasesHoy = \App\Models\Horario::join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->whereIn('asignaciones.seccion_id', $seccionesCoordinador)
                ->join('asistencias', 'horarios.id', '=', 'asistencias.horario_id')
                ->whereDate('asistencias.fecha', $fechaActual)
                ->count();
            $data['totalClasesHoy'] = $totalClasesHoy;

            $topClass = \App\Models\AsistenciaEstudiante::select('secciones.nombre as clase', \DB::raw('count(*) as total'))
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->join('secciones', 'asignaciones.seccion_id', '=', 'secciones.id')
                ->where('estado', 'A')
                ->whereIn('asignaciones.seccion_id', $seccionesCoordinador)
                ->whereDate('asistencias.fecha', $fechaActual)
                ->groupBy('secciones.nombre')
                ->orderBy('total', 'desc')
                ->first();
            $data['claseTop'] = $topClass ? $topClass->clase : 'N/A';
            $data['asistenciaClaseTop'] = $topClass ? round(($topClass->total / $totalStudents) * 100) : 0;

            $topSubject = \App\Models\AsistenciaEstudiante::select('materias.nombre as materia', \DB::raw('count(*) as total'))
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->join('materias', 'asignaciones.materia_id', '=', 'materias.id')
                ->where('estado', 'A')
                ->whereIn('asignaciones.seccion_id', $seccionesCoordinador)
                ->whereDate('asistencias.fecha', $fechaActual)
                ->groupBy('materias.nombre')
                ->orderBy('total', 'desc')
                ->first();
            $data['materiaTop'] = $topSubject ? $topSubject->materia : 'N/A';
            $data['asistenciaMateriaTop'] = $topSubject ? round(($topSubject->total / $totalStudents) * 100) : 0;

            $data['totalSecciones'] = \App\Models\Seccion::whereIn('id', $seccionesCoordinador)->count();
            $data['totalMaterias'] = \App\Models\Materia::whereHas('asignaciones', function ($query) use ($seccionesCoordinador) {
                $query->whereIn('seccion_id', $seccionesCoordinador);
            })->distinct()->count();

            $data['estudiantesPorSeccion'] = \App\Models\Seccion::whereIn('id', $seccionesCoordinador)
                ->withCount('estudiantes')
                ->whereHas('estudiantes')
                ->get()
                ->map(function ($seccion) {
                    return [
                        'nombre' => $seccion->nombre,
                        'total' => $seccion->estudiantes_count
                    ];
                });

            $data['profesoresPorMateria'] = \App\Models\Materia::with(['profesores'])
                ->whereHas('profesores')
                ->get()
                ->map(function ($materia) {
                    return [
                        'nombre' => $materia->nombre,
                        'total' => $materia->profesores->count()
                    ];
                });

            $fechaInicio = now('America/Caracas')->subDays(14);
            $last30Days = now('America/Caracas')->subDays(30);
            $attendanceByDay = collect([]);

            $totalStudents = \App\Models\Estudiante::count();

            $allAttendance = \App\Models\AsistenciaEstudiante::where('estado', 'A')
                ->whereBetween('created_at', [$last30Days, now('America/Caracas')])
                ->get();

            Log::info('Total attendance records:', ['count' => $allAttendance->count()]);

            $attendanceRecords = \App\Models\AsistenciaEstudiante::with('asistencia')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->where('asistencia_estudiante.estado', 'A')
                ->whereBetween('asistencias.fecha', [$last30Days, now('America/Caracas')])
                ->get();

            Log::info('Attendance records count:', ['count' => $attendanceRecords->count()]);

            $attendanceCounts = \App\Models\AsistenciaEstudiante::select(
                \DB::raw('"horarios"."dia" as dia'),
                \DB::raw('count(*) as total_asistencias')
            )
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->where('asistencia_estudiante.estado', 'A')
                ->when($user->hasRole('coordinador'), function ($query) use ($user) {
                    $query->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                        ->whereIn('asignaciones.seccion_id', $user->secciones->pluck('id'));
                })
                ->when($user->hasRole('profesor'), function ($query) use ($user) {
                    $query->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                        ->join('profesores', 'asignaciones.profesor_id', '=', 'profesores.id')
                        ->where('profesores.user_id', $user->id);
                })
                ->groupBy('horarios.dia')
                ->get();

            Log::info('Raw attendance data:', ['data' => $attendanceCounts->toArray()]);

            $attendanceByDay = $attendanceCounts
                ->groupBy(function ($item) {
                    Log::info('Processing day:', ['day' => $item->dia]);
                    return $item->dia;
                })
                ->map(function ($group) {
                    Log::info('Group data:', ['count' => $group->sum('total_asistencias')]);
                    return [
                        'dia' => $group[0]->dia,
                        'total_asistencias' => $group->sum('total_asistencias')
                    ];
                });

            Log::info('Final attendance data:', ['data' => $attendanceByDay->toArray()]);

            $attendanceByDay = $attendanceByDay->map(function ($dayData) use ($totalStudents) {
                return [
                    'dia' => $dayData['dia'],
                    'tasa' => round(($dayData['total_asistencias'] / $totalStudents) * 100, 2)
                ];
            })->values();

            // Initialize with all days at 0%
            $attendanceByDay = collect([
                ['dia' => 'Lunes', 'tasa' => 0],
                ['dia' => 'Martes', 'tasa' => 0],
                ['dia' => 'Miércoles', 'tasa' => 0],
                ['dia' => 'Jueves', 'tasa' => 0],
                ['dia' => 'Viernes', 'tasa' => 0],
                ['dia' => 'Sábado', 'tasa' => 0],
                ['dia' => 'Domingo', 'tasa' => 0]
            ]);

            $attendanceData = \DB::table('asistencia_estudiante')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->join('secciones', 'asignaciones.seccion_id', '=', 'secciones.id')
                ->join('estudiantes', 'secciones.id', '=', 'estudiantes.seccion_id')
                ->where('asistencia_estudiante.estado', 'A')
                ->whereBetween('asistencias.fecha', [Carbon::now('America/Caracas')->subDays(30), Carbon::now('America/Caracas')])
                ->when($user->hasRole('coordinador'), function ($query) use ($user) {
                    $query->whereIn('secciones.id', $user->secciones->pluck('id'));
                })
                ->select(
                    'horarios.dia',
                    \DB::raw('count(DISTINCT asistencia_estudiante.estudiante_id) as estudiantes_unicos')
                )
                ->groupBy('horarios.dia')
                ->get();

            if (!$attendanceData->isEmpty()) {
                $totalStudents = \App\Models\Estudiante::whereIn('seccion_id', $seccionesCoordinador)->count();

                foreach ($attendanceData as $item) {
                    $attendanceRate = ($totalStudents > 0) ? round(($item->estudiantes_unicos / $totalStudents) * 100) : 0;
                    $attendanceByDay = $attendanceByDay->map(function ($day) use ($item, $attendanceRate) {
                        if ($day['dia'] === $item->dia) {
                            $day['tasa'] = $attendanceRate;
                        }
                        return $day;
                    });
                }
            }

            Log::info('Final attendance by day data:', ['data' => $attendanceByDay->toArray()]);

            $data['attendanceByDay'] = $attendanceByDay;
        } elseif ($user->hasRole('profesor') && $user->profesor) {



            $fechaInicio = now('America/Caracas')->subDays(30);
            $fechaFin = now('America/Caracas');

            $attendanceTrend = collect([]);

            for ($i = 0; $i < 30; $i++) {
                $fecha = $fechaInicio->copy()->addDays($i);
                $attendanceTrend->push([
                    'date' => $fecha->format('Y-m-d'),
                    'count' => rand(80, 150)
                ]);
            }

            $data['attendanceTrend'] = $attendanceTrend;

            $profesor = $user->profesor;
            $fechaActual = $data['fechaActual'];

            $dias = [
                'Monday' => 'Lunes',
                'Tuesday' => 'Martes',
                'Wednesday' => 'Miércoles',
                'Thursday' => 'Jueves',
                'Friday' => 'Viernes',
                'Saturday' => 'Sábado',
                'Sunday' => 'Domingo'
            ];

            $diaActual = $dias[$fechaActual->format('l')];

            $asignacionesProfesor = Asignacion::where('profesor_id', $profesor->id)->pluck('id');

            // Ordenar por hora de inicio (convertir string a time para evitar orden lexicográfico)
            $data['horarioHoy'] = Horario::whereIn('asignacion_id', $asignacionesProfesor)
                ->where('dia', $diaActual)
                ->with(['asignacion.materia', 'asignacion.seccion', 'asignacion.seccion.grado'])
                // PostgreSQL: convertir la cadena a time para ordenar correctamente
                ->orderByRaw("hora_inicio::time ASC")
                ->get()
                ->filter(function ($clase) {
                    return $clase->asignacion && $clase->asignacion->seccion && $clase->asignacion->seccion->grado;
                });

            $data['asistenciasHoy'] = Asistencia::where('profesor_id', $profesor->id)
                ->whereDate('fecha', $fechaActual)
                ->with(['estudiantes', 'materia', 'horario'])
                ->get();

            $data['totalClases'] = $data['horarioHoy']->count();

            $data['clasesConAsistencia'] = $data['asistenciasHoy']->filter(function ($asistencia) use ($fechaActual, $data) {
                return $asistencia->fecha->toDateString() === $fechaActual->toDateString() &&
                    $data['horarioHoy']->contains('id', $asistencia->horario_id);
            })->unique('horario_id')->count();

            $data['porcentajeAsistencia'] = $data['totalClases'] > 0 ?
                round(($data['clasesConAsistencia'] / $data['totalClases']) * 100) : 0;

            $data['secciones'] = Asignacion::whereIn('id', $asignacionesProfesor)
                ->with('seccion')
                ->get()
                ->pluck('seccion')
                ->unique('id');

            $data['estudiantesConMasFaltas'] = AsistenciaEstudiante::where('estado', 'I')
                ->whereHas('asistencia', function ($query) use ($profesor) {
                    $query->where('profesor_id', $profesor->id);
                })
                ->with('estudiante')
                ->selectRaw('estudiante_id, COUNT(*) as faltas')
                ->groupBy('estudiante_id')
                ->orderByDesc('faltas')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return $item->estudiante->toArray() + ['faltas' => $item->faltas];
                });

            // Asegurarse de ordenar las próximas clases por hora convirtiendo a Carbon (maneja formatos no paddeados)
            $data['proximasClases'] = $data['horarioHoy']->sortBy(function ($clase) {
                try {
                    return Carbon::createFromFormat('H:i', $clase->hora_inicio);
                } catch (\Exception $e) {
                    // Si el formato no coincide, devolver la cadena para fallback
                    return $clase->hora_inicio;
                }
            })->values()->take(3);

            $data['asistenciasPorMateria'] = $data['asistenciasHoy']->filter(function ($asistencia) {
                return $asistencia->materia;
            });

            $data['asistenciaPorEstado'] = $data['asistenciasHoy']->flatMap(function ($asistencia) {
                return $asistencia->estudiantes;
            })->groupBy('estado')->map->count();

            $data['estadosAsistencia'] = $data['asistenciaPorEstado'];

            $data['estudiantesPorSeccion'] = Seccion::withCount('estudiantes')
                ->whereHas('estudiantes')
                ->get()
                ->map(function ($seccion) {
                    return [
                        'nombre' => $seccion->nombre,
                        'total' => $seccion->estudiantes_count
                    ];
                });

            $data['profesoresPorMateria'] = Materia::with(['profesores'])
                ->whereHas('profesores')
                ->get()
                ->map(function ($materia) {
                    return [
                        'nombre' => $materia->nombre,
                        'total' => $materia->profesores->count()
                    ];
                });

            $fechaInicio = now('America/Caracas')->subDays(30);
            $data['attendanceTrend'] = Asistencia::whereBetween('fecha', [$fechaInicio, now('America/Caracas')])
                ->select(
                    DB::raw('DATE(fecha) as date'),
                    DB::raw('COUNT(*) as attendance_count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->havingRaw('COUNT(*) > 0')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'count' => (int) $item->attendance_count
                    ];
                });

            if ($data['estudiantesPorSeccion']->isEmpty()) {
                $data['estudiantesPorSeccion'] = collect([
                    ['nombre' => 'Sección A', 'total' => 25],
                    ['nombre' => 'Sección B', 'total' => 30],
                    ['nombre' => 'Sección C', 'total' => 20]
                ]);
            }

            if ($data['profesoresPorMateria']->isEmpty()) {
                $data['profesoresPorMateria'] = collect([
                    ['nombre' => 'Matemáticas', 'total' => 5],
                    ['nombre' => 'Español', 'total' => 4],
                    ['nombre' => 'Inglés', 'total' => 3]
                ]);
            }

            if ($data['attendanceTrend']->isEmpty()) {
                $data['attendanceTrend'] = collect([
                    ['date' => now('America/Caracas')->subDays(29)->format('Y-m-d'), 'count' => 100],
                    ['date' => now('America/Caracas')->subDays(28)->format('Y-m-d'), 'count' => 120],
                    ['date' => now('America/Caracas')->subDays(27)->format('Y-m-d'), 'count' => 110],
                    ['date' => now('America/Caracas')->subDays(26)->format('Y-m-d'), 'count' => 130],
                    ['date' => now('America/Caracas')->subDays(25)->format('Y-m-d'), 'count' => 140],
                    ['date' => now('America/Caracas')->subDays(24)->format('Y-m-d'), 'count' => 150]
                ]);
            }
        }

        return view('dashboard', $data);
    }
}
