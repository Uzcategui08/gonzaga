<?php

namespace App\Http\Controllers;

use App\Models\AsistenciaEstudiante;
use App\Models\Seccion;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AsistenciaCoordinadorController extends Controller
{
    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Acceso no autorizado');
        }

        if (!method_exists($user, 'hasRole')) {
            abort(403, 'Acceso no autorizado');
        }

        $isAdmin = $user->hasRole('admin');
        $isCoordinador = $user->hasRole('coordinador');

        if (!$isAdmin && !$isCoordinador) {
            abort(403, 'Acceso no autorizado');
        }

        [$startDate, $endDate] = $this->resolveDateRange($request);

        $summary = $this->buildSummary($user, $isAdmin, $startDate, $endDate);

        return view('asistencias.coordinador-index', array_merge($summary, [
            'isAdmin' => $isAdmin,
            'filters' => [
                'start_date' => $startDate ? $startDate->toDateString() : null,
                'end_date' => $endDate ? $endDate->toDateString() : null,
            ],
        ]));
    }

    public function exportPdf(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !method_exists($user, 'hasRole')) {
            abort(403, 'Acceso no autorizado');
        }

        $isAdmin = $user->hasRole('admin');
        $isCoordinador = $user->hasRole('coordinador');

        if (!$isAdmin && !$isCoordinador) {
            abort(403, 'Acceso no autorizado');
        }

        [$startDate, $endDate] = $this->resolveDateRange($request);

        $summary = $this->buildSummary($user, $isAdmin, $startDate, $endDate);

        $pdf = Pdf::loadView('asistencias.coordinador-pdf', $summary + [
            'generatedAt' => now(),
            'usuario' => $user,
            'filters' => [
                'start_date' => $startDate ? $startDate->toDateString() : null,
                'end_date' => $endDate ? $endDate->toDateString() : null,
            ],
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('resumen_inasistencias_' . now()->format('Y-m-d_His') . '.pdf');
    }

    private function buildSummary(User $user, bool $isAdmin, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        if ($isAdmin) {
            $secciones = Seccion::with(['grado:id,nombre'])
                ->orderBy('nombre')
                ->get();
        } else {
            $secciones = $user->secciones ?? collect();

            if (method_exists($secciones, 'load')) {
                $secciones->load(['grado:id,nombre']);
            }

            $secciones = $secciones->sortBy('nombre')->values();
        }

        $seccionIds = $secciones->pluck('id');

        $sectionsData = [];

        foreach ($secciones as $seccion) {
            $sectionsData[$seccion->id] = [
                'seccion_id' => $seccion->id,
                'seccion' => $seccion->nombre,
                'grado' => optional($seccion->grado)->nombre ?? 'Sin grado',
                'total_inasistencias' => 0,
                'total_multiplicado' => 0,
                'estudiantes' => [],
            ];
        }

        $totalInasistencias = 0;
        $totalMultiplicado = 0;
        $totalEstudiantes = 0;

        if ($seccionIds->isNotEmpty()) {
            $registros = AsistenciaEstudiante::select(
                    'secciones.id as seccion_id',
                    'secciones.nombre as seccion_nombre',
                    'grados.nombre as grado_nombre',
                    'estudiantes.id as estudiante_id',
                    'estudiantes.nombres',
                    'estudiantes.apellidos'
                )
                ->selectRaw('COUNT(*) as total_inasistencias')
                ->join('asistencias', 'asistencias.id', '=', 'asistencia_estudiante.asistencia_id')
                ->join('horarios', 'horarios.id', '=', 'asistencias.horario_id')
                ->join('asignaciones', 'asignaciones.id', '=', 'horarios.asignacion_id')
                ->join('secciones', 'secciones.id', '=', 'asignaciones.seccion_id')
                ->leftJoin('grados', 'grados.id', '=', 'secciones.grado_id')
                ->join('estudiantes', 'estudiantes.id', '=', 'asistencia_estudiante.estudiante_id')
                ->where('asistencia_estudiante.estado', 'I')
                ->whereIn('secciones.id', $seccionIds)
                ->when($startDate, function ($query) use ($startDate) {
                    $query->whereDate('asistencias.fecha', '>=', $startDate->toDateString());
                })
                ->when($endDate, function ($query) use ($endDate) {
                    $query->whereDate('asistencias.fecha', '<=', $endDate->toDateString());
                })
                ->groupBy(
                    'secciones.id',
                    'secciones.nombre',
                    'grados.nombre',
                    'estudiantes.id',
                    'estudiantes.nombres',
                    'estudiantes.apellidos'
                )
                ->orderBy('grados.nombre')
                ->orderBy('secciones.nombre')
                ->orderBy('estudiantes.apellidos')
                ->orderBy('estudiantes.nombres')
                ->get();

            $totalEstudiantes = $registros->count();

            foreach ($registros as $registro) {
                $count = (int) $registro->total_inasistencias;
                $valorDoble = $count * 2;
                $nombreEstudiante = trim($registro->nombres . ' ' . $registro->apellidos);

                if (!isset($sectionsData[$registro->seccion_id])) {
                    $sectionsData[$registro->seccion_id] = [
                        'seccion_id' => $registro->seccion_id,
                        'seccion' => $registro->seccion_nombre,
                        'grado' => $registro->grado_nombre ?? 'Sin grado',
                        'total_inasistencias' => 0,
                        'total_multiplicado' => 0,
                        'estudiantes' => [],
                    ];
                }

                $sectionsData[$registro->seccion_id]['grado'] = $sectionsData[$registro->seccion_id]['grado'] ?? ($registro->grado_nombre ?? 'Sin grado');

                $sectionsData[$registro->seccion_id]['estudiantes'][] = [
                    'estudiante_id' => $registro->estudiante_id,
                    'estudiante' => $nombreEstudiante,
                    'inasistencias' => $count,
                    'valor_doble' => $valorDoble,
                ];

                $sectionsData[$registro->seccion_id]['total_inasistencias'] += $count;
                $sectionsData[$registro->seccion_id]['total_multiplicado'] += $valorDoble;

                $totalInasistencias += $count;
                $totalMultiplicado += $valorDoble;
            }
        }

        $sectionsCollection = collect($sectionsData)
            ->sortBy(function ($section) {
                return sprintf('%s_%s', $section['grado'], $section['seccion']);
            }, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $seccionesConInasistencias = $sectionsCollection->filter(function ($section) {
            return !empty($section['estudiantes']);
        })->count();

        return [
            'sections' => $sectionsCollection,
            'totalInasistencias' => $totalInasistencias,
            'totalMultiplicado' => $totalMultiplicado,
            'totalEstudiantes' => $totalEstudiantes,
            'totalSeccionesAsignadas' => count($sectionsData),
            'totalSeccionesConInasistencias' => $seccionesConInasistencias,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    private function resolveDateRange(Request $request): array
    {
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        $startDate = null;
        $endDate = null;

        try {
            if ($startDateInput) {
                $startDate = Carbon::createFromFormat('Y-m-d', $startDateInput)->startOfDay();
            }
        } catch (\Throwable $exception) {
            $startDate = null;
        }

        try {
            if ($endDateInput) {
                $endDate = Carbon::createFromFormat('Y-m-d', $endDateInput)->endOfDay();
            }
        } catch (\Throwable $exception) {
            $endDate = null;
        }

        if ($startDate && $endDate && $startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [
                $endDate->copy()->startOfDay(),
                $startDate->copy()->endOfDay(),
            ];
        }

        if (!$startDate && !$endDate) {
            $now = Carbon::now();
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        }

        if ($startDate && !$endDate) {
            $endDate = $startDate->copy()->endOfMonth();
        }

        if (!$startDate && $endDate) {
            $startDate = $endDate->copy()->startOfMonth();
        }

        return [$startDate, $endDate];
    }
}
