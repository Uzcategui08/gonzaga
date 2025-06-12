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
            'attendanceTrend' => collect()
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

        if ($user->hasRole('admin') || $user->hasRole('coordinador')) {
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

            $data['tardiosHoy'] = \App\Models\AsistenciaEstudiante::where('asistencia_estudiante.estado', 'P')
                ->join('asistencias', 'asistencia_estudiante.asistencia_id', '=', 'asistencias.id')
                ->join('horarios', 'asistencias.horario_id', '=', 'horarios.id')
                ->join('asignaciones', 'horarios.asignacion_id', '=', 'asignaciones.id')
                ->whereDate('asistencias.fecha', $fechaActual)
                ->distinct('estudiante_id')
                ->count('estudiante_id');    

            $data['totalSecciones'] = \App\Models\Seccion::count();
            $data['totalMaterias'] = \App\Models\Materia::count();

            $data['estudiantesPorSeccion'] = \App\Models\Seccion::withCount('estudiantes')
                ->whereHas('estudiantes')
                ->get()
                ->map(function($seccion) {
                    return [
                        'nombre' => $seccion->nombre,
                        'total' => $seccion->estudiantes_count
                    ];
                });

            $data['profesoresPorMateria'] = \App\Models\Materia::with(['profesores'])
                ->whereHas('profesores')
                ->get()
                ->map(function($materia) {
                    return [
                        'nombre' => $materia->nombre,
                        'total' => $materia->profesores->count()
                    ];
                });

            $fechaInicio = now('America/Caracas')->subDays(30);
            $data['attendanceTrend'] = \App\Models\Asistencia::whereBetween('fecha', [$fechaInicio, now('America/Caracas')])
                ->select(
                    DB::raw('DATE(fecha) as date'),
                    DB::raw('COUNT(*) as attendance_count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function($item) {
                    return [
                        'date' => $item->date,
                        'count' => (int) $item->attendance_count
                    ];
                })
                ->filter(function($item) {
                    return $item['count'] > 0;
                });

            if ($data['attendanceTrend']->isEmpty()) {
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
            }

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
            
        } elseif ($user->hasRole('profesor') && $user->profesor) {

            $data['estudiantesPorSeccion'] = collect([
                ['nombre' => 'Sección A', 'total' => 25],
                ['nombre' => 'Sección B', 'total' => 30],
                ['nombre' => 'Sección C', 'total' => 20]
            ]);

            $data['profesoresPorMateria'] = collect([
                ['nombre' => 'Matemáticas', 'total' => 5],
                ['nombre' => 'Español', 'total' => 4],
                ['nombre' => 'Inglés', 'total' => 3]
            ]);

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
            
            $data['horarioHoy'] = Horario::whereIn('asignacion_id', $asignacionesProfesor)
                ->where('dia', $diaActual)
                ->with(['asignacion.materia', 'asignacion.seccion', 'asignacion.seccion.grado'])
                ->get()
                ->filter(function($clase) {
                    return $clase->asignacion && $clase->asignacion->seccion && $clase->asignacion->seccion->grado;
                });

            $data['asistenciasHoy'] = Asistencia::where('profesor_id', $profesor->id)
                ->whereDate('fecha', $fechaActual)
                ->with(['estudiantes', 'materia', 'horario'])
                ->get();
            
            $data['totalClases'] = $data['horarioHoy']->count();
            
            $data['clasesConAsistencia'] = $data['asistenciasHoy']->filter(function($asistencia) use ($fechaActual, $data) {
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
                ->whereHas('asistencia', function($query) use ($profesor) {
                    $query->where('profesor_id', $profesor->id);
                })
                ->with('estudiante')
                ->selectRaw('estudiante_id, COUNT(*) as faltas')
                ->groupBy('estudiante_id')
                ->orderByDesc('faltas')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    return $item->estudiante->toArray() + ['faltas' => $item->faltas];
                });

            $data['proximasClases'] = $data['horarioHoy']->sortBy('hora_inicio')->take(3);

            $data['asistenciasPorMateria'] = $data['asistenciasHoy']->filter(function($asistencia) {
                return $asistencia->materia;
            });
            
            $data['asistenciaPorEstado'] = $data['asistenciasHoy']->flatMap(function($asistencia) {
                return $asistencia->estudiantes;
            })->groupBy('estado')->map->count();
            
            $data['estadosAsistencia'] = $data['asistenciaPorEstado'];

            $data['estudiantesPorSeccion'] = Seccion::withCount('estudiantes')
                ->whereHas('estudiantes') 
                ->get()
                ->map(function($seccion) {
                    return [
                        'nombre' => $seccion->nombre,
                        'total' => $seccion->estudiantes_count
                    ];
                });

            $data['profesoresPorMateria'] = Materia::with(['profesores'])
                ->whereHas('profesores') 
                ->get()
                ->map(function($materia) {
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
                ->map(function($item) {
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