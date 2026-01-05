<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Asistencia;
use App\Models\AsistenciaEstudiante;
use App\Models\Estudiante;
use App\Models\Horario;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\Seccion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

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

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('secretaria')) {
            return redirect()->route('asistencias.secretaria.index');
        }

        if ($user && $user->profesor) {
            $data['notifications'] = $user->profesor->notifications()->latest()->get();

            $fechaActual = now('America/Caracas');

            // Total de estudiantes del profesor: sumar estudiantes de las secciones donde tiene clases hoy
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

            $asignacionesProfesor = Asignacion::where('profesor_id', $user->profesor->id)->pluck('id');
            $seccionesHoy = Horario::whereIn('asignacion_id', $asignacionesProfesor)
                ->where('dia', $diaActual)
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->pluck('asignaciones.seccion_id')
                ->unique();

            $data['totalEstudiantesProfesor'] = \App\Models\Estudiante::whereIn('seccion_id', $seccionesHoy)->distinct('id')->count('id');

            // Inasistencias del profesor hoy: estudiantes con estado I en cualquier asistencia del profesor hoy
            $data['inasistenciasProfesor'] = \App\Models\AsistenciaEstudiante::where('estado', 'I')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->where('asistencias.profesor_id', $user->profesor->id)
                ->whereDate('asistencias.fecha', $fechaActual)
                ->distinct('estudiante_id')
                ->count('estudiante_id');
        }

        if ($user && $user->hasRole('admin')) {
            $data['totalEstudiantes'] = \App\Models\Estudiante::count();
            $data['totalProfesores'] = \App\Models\Profesor::count();
            $fechaActual = now('America/Caracas');

            $latestAsistenciaIds = $this->latestAsistenciaIdsForDateByHorario($fechaActual);
            $attendanceCounts = $this->countAttendanceRowsByState($latestAsistenciaIds);

            $data['asistenciasHoy'] = $attendanceCounts['A'] + $attendanceCounts['P'];
            $data['tardiosHoy'] = $attendanceCounts['P'];
            $data['inasistenciasHoy'] = $attendanceCounts['I'];
            $data['totalClasesHoy'] = $latestAsistenciaIds->count();

            $attendanceByDay = DB::table('asistencia_estudiante')
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
                    DB::raw('count(*) as total_asistencias'),
                    DB::raw('count(DISTINCT asistencia_estudiante.estudiante_id) as estudiantes_unicos')
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

            $last30Days = Carbon::now('America/Caracas')->subDays(30)->startOfDay();
            $todayEnd = $fechaActual->copy()->endOfDay();

            $stateTotals30 = AsistenciaEstudiante::select('asistencia_estudiante.estado', DB::raw('count(*) as total'))
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->whereBetween('asistencias.fecha', [$last30Days->toDateString(), $todayEnd->toDateString()])
                ->whereIn('asistencia_estudiante.estado', ['A', 'P', 'I'])
                ->groupBy('asistencia_estudiante.estado')
                ->pluck('total', 'estado')
                ->map(function ($value) {
                    return (int) $value;
                })
                ->toArray();

            $present30 = ($stateTotals30['A'] ?? 0) + ($stateTotals30['P'] ?? 0);
            $total30 = $present30 + ($stateTotals30['I'] ?? 0);
            $data['promedioAsistencia'] = $total30 > 0 ? round(($present30 / $total30) * 100) : 0;

            $periods = \App\Models\Horario::select('dia', DB::raw('count(*) as total'))
                ->whereHas('asistencia', function ($query) use ($fechaActual) {
                    $query->whereDate('fecha', $fechaActual);
                })
                ->groupBy('dia')
                ->orderBy('total', 'desc')
                ->first();
            $data['periodoMasActivo'] = $periods ? $periods->dia : 'N/A';
            $data['totalClasesPeriodo'] = $periods ? $periods->total : 0;

            $topClass = \App\Models\AsistenciaEstudiante::select('secciones.nombre as clase', DB::raw('count(*) as total'))
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

            $topSubject = \App\Models\AsistenciaEstudiante::select('materias.nombre as materia', DB::raw('count(*) as total'))
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

            $data['levelBreakdown'] = $this->buildLevelBreakdown(null, $fechaActual);
        } elseif ($user && $user->hasRole('coordinador')) {
            $seccionesCoordinador = $user->secciones->pluck('id');

            $data['totalEstudiantes'] = \App\Models\Estudiante::whereIn('seccion_id', $seccionesCoordinador)->count();
            $data['totalProfesores'] = \App\Models\Profesor::whereHas('secciones', function ($query) use ($seccionesCoordinador) {
                $query->whereIn('secciones.id', $seccionesCoordinador);
            })->distinct()->count();

            $fechaActual = now('America/Caracas');
            $diaActual = $fechaActual->format('l');

            $latestAsistenciaIds = $this->latestAsistenciaIdsForDateByHorario($fechaActual, $seccionesCoordinador);
            $attendanceCounts = $this->countAttendanceRowsByState($latestAsistenciaIds);

            $data['asistenciasHoy'] = $attendanceCounts['A'] + $attendanceCounts['P'];
            $data['tardiosHoy'] = $attendanceCounts['P'];
            $data['inasistenciasHoy'] = $attendanceCounts['I'];

            $data['totalClasesHoy'] = $latestAsistenciaIds->count();

            $last30Days = Carbon::now('America/Caracas')->subDays(30)->startOfDay();
            $todayEnd = $fechaActual->copy()->endOfDay();

            $stateTotals30 = AsistenciaEstudiante::select('asistencia_estudiante.estado', DB::raw('count(*) as total'))
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->whereIn('asignaciones.seccion_id', $seccionesCoordinador)
                ->whereBetween('asistencias.fecha', [$last30Days->toDateString(), $todayEnd->toDateString()])
                ->whereIn('asistencia_estudiante.estado', ['A', 'P', 'I'])
                ->groupBy('asistencia_estudiante.estado')
                ->pluck('total', 'estado')
                ->map(function ($value) {
                    return (int) $value;
                })
                ->toArray();

            $present30 = ($stateTotals30['A'] ?? 0) + ($stateTotals30['P'] ?? 0);
            $total30 = $present30 + ($stateTotals30['I'] ?? 0);
            $data['promedioAsistencia'] = $total30 > 0 ? round(($present30 / $total30) * 100) : 0;

            // totalClasesHoy set from latest asistencias per horario above

            $topClass = \App\Models\AsistenciaEstudiante::select('secciones.nombre as clase', DB::raw('count(*) as total'))
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
            $data['asistenciaClaseTop'] = $topClass ? (int) $topClass->total : 0;

            $topSubject = \App\Models\AsistenciaEstudiante::select('materias.nombre as materia', DB::raw('count(*) as total'))
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
            $data['asistenciaMateriaTop'] = $topSubject ? (int) $topSubject->total : 0;

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
                DB::raw('"horarios"."dia" as dia'),
                DB::raw('count(*) as total_asistencias')
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

            $attendanceData = DB::table('asistencia_estudiante')
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
                    DB::raw('count(DISTINCT asistencia_estudiante.estudiante_id) as estudiantes_unicos')
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

            $data['levelBreakdown'] = $this->buildLevelBreakdown($seccionesCoordinador, $fechaActual);
        } elseif ($user->hasRole('profesor') && $user->profesor) {



            $fechaInicio = now('America/Caracas')->subDays(30);
            $fechaFin = now('America/Caracas');

            // Tendencia real de asistencias del profesor últimos 30 días (conteo por fecha)
            $data['attendanceTrend'] = Asistencia::where('profesor_id', $user->profesor->id)
                ->whereBetween('fecha', [$fechaInicio, now('America/Caracas')])
                ->select(DB::raw('DATE(fecha) as date'), DB::raw('COUNT(*) as attendance_count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'count' => (int) $item->attendance_count
                    ];
                });

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

    /**
     * Retrieve the latest asistencia ids for each horario on a given date.
     *
     * This avoids double-counting when the same class is re-registered.
     */
    private function latestAsistenciaIdsForDateByHorario(Carbon $date, $seccionIds = null): Collection
    {
        $query = Asistencia::select('asistencias.id', 'horarios.id as horario_id')
            ->join('horarios', 'horarios.id', '=', 'asistencias.horario_id')
            ->join('asignaciones', 'asignaciones.id', '=', 'horarios.asignacion_id')
            ->join('secciones', 'secciones.id', '=', 'asignaciones.seccion_id')
            ->whereDate('asistencias.fecha', $date->toDateString())
            ->orderBy('asistencias.created_at', 'desc')
            ->orderBy('asistencias.id', 'desc');

        if ($seccionIds !== null) {
            $ids = $seccionIds instanceof Collection ? $seccionIds : collect($seccionIds);
            if ($ids->isNotEmpty()) {
                $query->whereIn('secciones.id', $ids->all());
            }
        }

        return $query->get()
            ->groupBy('horario_id')
            ->map(function ($items) {
                return optional($items->first())->id;
            })
            ->filter()
            ->values();
    }

    /**
     * Count attendance rows per state for provided asistencias.
     */
    private function countAttendanceRowsByState(Collection $asistenciaIds): array
    {
        if ($asistenciaIds->isEmpty()) {
            return ['A' => 0, 'P' => 0, 'I' => 0];
        }

        $counts = AsistenciaEstudiante::select('estado', DB::raw('count(*) as total'))
            ->whereIn('asistencia_id', $asistenciaIds->all())
            ->whereIn('estado', ['A', 'P', 'I'])
            ->groupBy('estado')
            ->get()
            ->pluck('total', 'estado')
            ->map(function ($value) {
                return (int) $value;
            })
            ->toArray();

        return array_merge(['A' => 0, 'P' => 0, 'I' => 0], $counts);
    }

    /**
     * Build dashboard metrics grouped by nivel (primaria/secundaria).
     */
    private function buildLevelBreakdown(?Collection $restrictedSectionIds, Carbon $fechaActual): array
    {
        $dayMap = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];
        $diaActual = $dayMap[$fechaActual->format('l')] ?? $fechaActual->format('l');

        $seccionesPorNivel = Seccion::select('secciones.id', 'grados.nivel')
            ->join('grados', 'grados.id', '=', 'secciones.grado_id')
            ->when($restrictedSectionIds && $restrictedSectionIds->isNotEmpty(), function ($query) use ($restrictedSectionIds) {
                $query->whereIn('secciones.id', $restrictedSectionIds->all());
            })
            ->get()
            ->groupBy(function ($seccion) {
                return $seccion->nivel ?? 'Sin nivel';
            });

        if ($seccionesPorNivel->isEmpty()) {
            return [];
        }

        return $seccionesPorNivel->map(function ($secciones, $nivel) use ($fechaActual, $diaActual) {
            $sectionIds = $secciones->pluck('id');

            $studentCount = Estudiante::whereIn('seccion_id', $sectionIds)->count();
            $teacherCount = Profesor::whereHas('asignaciones', function ($query) use ($sectionIds) {
                $query->whereIn('seccion_id', $sectionIds);
            })->distinct('profesores.id')->count('profesores.id');

            $classesScheduled = Horario::whereHas('asignacion', function ($query) use ($sectionIds) {
                $query->whereIn('seccion_id', $sectionIds);
            })->where('dia', $diaActual)->count();

            $classesWithAttendance = Asistencia::whereDate('fecha', $fechaActual)
                ->whereHas('horario.asignacion', function ($query) use ($sectionIds) {
                    $query->whereIn('seccion_id', $sectionIds);
                })
                ->distinct('horario_id')
                ->count('horario_id');

            $latestIds = $this->latestAsistenciaIdsForDateByHorario($fechaActual, $sectionIds);
            $stateCounts = $this->countAttendanceRowsByState($latestIds);
            $totalEvents = array_sum($stateCounts);

            $coverage = $classesScheduled > 0
                ? round(($classesWithAttendance / max($classesScheduled, 1)) * 100)
                : 0;

            return [
                'nivel' => $nivel,
                'secciones' => $sectionIds->count(),
                'estudiantes' => $studentCount,
                'profesores' => $teacherCount,
                'clasesProgramadas' => $classesScheduled,
                'clasesConAsistencia' => $classesWithAttendance,
                'cobertura' => $coverage,
                'asistencias' => $stateCounts['A'],
                'tardios' => $stateCounts['P'],
                'inasistencias' => $stateCounts['I'],
                'totalEventos' => $totalEvents
            ];
        })->values()->toArray();
    }
}
