<?php

namespace App\Http\Controllers\Extracurricular;

use App\Http\Controllers\Controller;
use App\Models\AsistenciaExtracurricular;
use App\Models\ClaseExtracurricular;
use App\Models\Profesor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExtracurricularDashboardController extends Controller
{
    public function index()
    {
        $fechaActual = Carbon::now('America/Caracas');

        $usuario = Auth::user();
        $esProfesorExtracurricular = $usuario instanceof User
            ? $usuario->hasRole('profesor_extracurricular')
            : false;

        $isoHoy = $fechaActual->isoWeekday();

        $clasesQuery = ClaseExtracurricular::query()
            ->soloActivas()
            ->withCount('estudiantes');

        if ($esProfesorExtracurricular) {
            if (Schema::hasColumn('clases_extracurriculares', 'dia_semana')) {
                $clasesQuery->where('dia_semana', $isoHoy);
            }

            $profesorId = Profesor::where('user_id', $usuario->id)->value('id');
            if (!empty($profesorId)) {
                $clasesQuery->where('profesor_id', $profesorId);
            }
        }

        $clases = $clasesQuery->orderBy('nombre')->get();

        $asistenciasHoy = AsistenciaExtracurricular::whereDate('fecha', $fechaActual->toDateString())->pluck('id');

        $estadoHoy = $asistenciasHoy->isEmpty()
            ? collect([])
            : DB::table('asistencia_extracurricular_estudiante')
            ->whereIn('asistencia_extracurricular_id', $asistenciasHoy)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $presentes = (int) (($estadoHoy['A'] ?? 0) + ($estadoHoy['P'] ?? 0));
        $pases = (int) ($estadoHoy['P'] ?? 0);
        $inasistencias = (int) ($estadoHoy['I'] ?? 0);

        return view('extracurricular.index', [
            'fechaActual' => $fechaActual,
            'clases' => $clases,
            'totalClases' => $clases->count(),
            'asistenciasHoy' => $presentes,
            'tardiosHoy' => $pases,
            'inasistenciasHoy' => $inasistencias,
            'usuario' => $usuario,
        ]);
    }
}
