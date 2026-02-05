<?php

namespace App\Http\Controllers\Extracurricular;

use App\Http\Controllers\Controller;
use App\Models\AsistenciaExtracurricular;
use App\Models\ClaseExtracurricular;
use App\Models\Profesor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExtracurricularHistoricoController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $esProfesorExtracurricular = $usuario instanceof User
            ? $usuario->hasRole('profesor_extracurricular')
            : false;

        $validated = $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date',
            'clase_id' => 'nullable|integer|exists:clases_extracurriculares,id',
        ]);

        $hoy = Carbon::now('America/Caracas')->toDateString();

        $asistenciasQuery = AsistenciaExtracurricular::query()
            ->with(['clase' => function ($query) {
                $query->with(['profesor.user:id,name']);
            }]);

        $profesorId = null;
        if ($esProfesorExtracurricular && $usuario) {
            $profesorId = Profesor::where('user_id', $usuario->id)->value('id');
            if (!empty($profesorId)) {
                $asistenciasQuery->whereHas('clase', function ($query) use ($profesorId) {
                    $query->where('profesor_id', $profesorId);
                });
            }
        }

        if (!empty($validated['clase_id'])) {
            $asistenciasQuery->where('clase_extracurricular_id', $validated['clase_id']);
        }

        if (!empty($validated['desde'])) {
            $asistenciasQuery->whereDate('fecha', '>=', $validated['desde']);
        }

        if (!empty($validated['hasta'])) {
            $asistenciasQuery->whereDate('fecha', '<=', $validated['hasta']);
        } else {
            $asistenciasQuery->whereDate('fecha', '<=', $hoy);
        }

        $asistencias = $asistenciasQuery
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(30)
            ->withQueryString();

        $asistenciaIds = $asistencias->getCollection()->pluck('id');

        $conteos = $asistenciaIds->isEmpty()
            ? collect([])
            : DB::table('asistencia_extracurricular_estudiante')
            ->whereIn('asistencia_extracurricular_id', $asistenciaIds)
            ->select('asistencia_extracurricular_id', 'estado', DB::raw('count(*) as total'))
            ->groupBy('asistencia_extracurricular_id', 'estado')
            ->get();

        $statsPorAsistencia = $conteos
            ->groupBy('asistencia_extracurricular_id')
            ->map(function ($items) {
                $porEstado = $items->pluck('total', 'estado');
                $a = (int) ($porEstado['A'] ?? 0);
                $p = (int) ($porEstado['P'] ?? 0);
                $i = (int) ($porEstado['I'] ?? 0);

                return [
                    'presentes' => $a + $p,
                    'pases' => $p,
                    'inasistencias' => $i,
                    'total' => $a + $p + $i,
                ];
            });

        $clasesParaFiltro = ClaseExtracurricular::query()
            ->soloActivas()
            ->when(!empty($profesorId), fn($q) => $q->where('profesor_id', $profesorId))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('extracurricular.historico.index', [
            'asistencias' => $asistencias,
            'statsPorAsistencia' => $statsPorAsistencia,
            'clasesParaFiltro' => $clasesParaFiltro,
            'filtros' => [
                'desde' => $validated['desde'] ?? null,
                'hasta' => $validated['hasta'] ?? null,
                'clase_id' => $validated['clase_id'] ?? null,
            ],
        ]);
    }

    public function show(AsistenciaExtracurricular $asistencia)
    {
        $usuario = Auth::user();
        $esProfesorExtracurricular = $usuario instanceof User
            ? $usuario->hasRole('profesor_extracurricular')
            : false;

        $asistencia->load([
            'clase.profesor.user:id,name',
            'estudiantes.seccion.grado',
        ]);

        if ($esProfesorExtracurricular && $usuario) {
            $profesorId = Profesor::where('user_id', $usuario->id)->value('id');
            if (!empty($profesorId) && (int) $asistencia->clase?->profesor_id !== (int) $profesorId) {
                abort(403, 'No tiene permiso para ver esta asistencia.');
            }
        }

        $conteos = DB::table('asistencia_extracurricular_estudiante')
            ->where('asistencia_extracurricular_id', $asistencia->id)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $presentes = (int) (($conteos['A'] ?? 0) + ($conteos['P'] ?? 0));
        $pases = (int) ($conteos['P'] ?? 0);
        $inasistencias = (int) ($conteos['I'] ?? 0);

        return view('extracurricular.historico.show', [
            'asistencia' => $asistencia,
            'conteos' => [
                'presentes' => $presentes,
                'pases' => $pases,
                'inasistencias' => $inasistencias,
                'total' => $presentes + $inasistencias,
            ],
        ]);
    }
}
