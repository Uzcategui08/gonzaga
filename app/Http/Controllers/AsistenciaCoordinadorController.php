<?php

namespace App\Http\Controllers;

use App\Models\AsistenciaEstudiante;
use App\Models\Estudiante;
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

    $weekValue = $request->input('week');
    $referenceDateValue = $request->input('reference_date');

    if (!$referenceDateValue && $startDate) {
      $referenceDateValue = $startDate->toDateString();
    }

    if (!$weekValue && $startDate) {
      $weekValue = sprintf('%s-W%02d', $startDate->format('o'), (int) $startDate->format('W'));
    }

    $summary = $this->buildSummary($user, $isAdmin, $startDate, $endDate);

    return view('asistencias.coordinador-index', array_merge($summary, [
      'isAdmin' => $isAdmin,
      'filters' => [
        'start_date' => $startDate ? $startDate->toDateString() : null,
        'end_date' => $endDate ? $endDate->toDateString() : null,
        'week' => $weekValue,
        'reference_date' => $referenceDateValue,
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

    $weekValue = $request->input('week');
    $referenceDateValue = $request->input('reference_date');

    if (!$referenceDateValue && $startDate) {
      $referenceDateValue = $startDate->toDateString();
    }

    if (!$weekValue && $startDate) {
      $weekValue = sprintf('%s-W%02d', $startDate->format('o'), (int) $startDate->format('W'));
    }

    $summary = $this->buildSummary($user, $isAdmin, $startDate, $endDate);

    $pdf = Pdf::loadView('asistencias.coordinador-pdf', $summary + [
      'generatedAt' => now(),
      'usuario' => $user,
      'filters' => [
        'start_date' => $startDate ? $startDate->toDateString() : null,
        'end_date' => $endDate ? $endDate->toDateString() : null,
        'week' => $weekValue,
        'reference_date' => $referenceDateValue,
      ],
    ])->setPaper('a4', 'portrait');

    return $pdf->stream('resumen_inasistencias_' . now()->format('Y-m-d_His') . '.pdf');
  }

  public function exportStudentDetailPdf(Request $request, int $seccionId, int $estudianteId)
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

    $weekValue = $request->input('week');
    $referenceDateValue = $request->input('reference_date');

    if (!$referenceDateValue && $startDate) {
      $referenceDateValue = $startDate->toDateString();
    }

    if (!$weekValue && $startDate) {
      $weekValue = sprintf('%s-W%02d', $startDate->format('o'), (int) $startDate->format('W'));
    }

    $seccion = Seccion::with('grado')->findOrFail($seccionId);

    if (!$isAdmin) {
      $userSeccionesIds = collect($user->secciones ?? [])->pluck('id')->all();

      if (!in_array($seccionId, $userSeccionesIds, true)) {
        abort(403, 'Acceso no autorizado');
      }
    }

    $estudiante = Estudiante::findOrFail($estudianteId);

    $detalles = AsistenciaEstudiante::select(
      'asistencias.id as asistencia_id',
      'asistencias.fecha',
      'horarios.hora_inicio',
      'horarios.hora_fin',
      'materias.nombre as materia_nombre',
      'asistencia_estudiante.estado'
    )
      ->join('asistencias', 'asistencias.id', '=', 'asistencia_estudiante.asistencia_id')
      ->join('horarios', 'horarios.id', '=', 'asistencias.horario_id')
      ->join('asignaciones', 'asignaciones.id', '=', 'horarios.asignacion_id')
      ->join('secciones', 'secciones.id', '=', 'asignaciones.seccion_id')
      ->leftJoin('materias', 'materias.id', '=', 'asignaciones.materia_id')
      ->where('secciones.id', $seccionId)
      ->where('asistencia_estudiante.estudiante_id', $estudianteId)
      ->when($startDate, function ($query) use ($startDate) {
        $query->whereDate('asistencias.fecha', '>=', $startDate->toDateString());
      })
      ->when($endDate, function ($query) use ($endDate) {
        $query->whereDate('asistencias.fecha', '<=', $endDate->toDateString());
      })
      ->orderBy('asistencias.fecha')
      ->orderBy('horarios.hora_inicio')
      ->get()
      ->map(function ($row) {
        $fecha = $row->fecha instanceof Carbon ? $row->fecha->copy() : Carbon::parse($row->fecha);
        $horaInicio = $row->hora_inicio ? substr($row->hora_inicio, 0, 5) : null;
        $horaFin = $row->hora_fin ? substr($row->hora_fin, 0, 5) : null;

        $estado = $row->estado ?? 'A';
        $horasInasistencia = $estado === 'I' ? 2 : 0;

        return [
          'asistencia_id' => $row->asistencia_id,
          'fecha' => $fecha,
          'hora_inicio' => $horaInicio,
          'hora_fin' => $horaFin,
          'materia' => $row->materia_nombre ?? 'Sin materia',
          'estado' => $estado,
          'horas_inasistencia' => $horasInasistencia,
        ];
      });

    $totales = [
      'P' => 0,
      'A' => 0,
      'I' => 0,
    ];

    $totalHoras = 0;

    foreach ($detalles as $detalle) {
      $estado = $detalle['estado'];
      if (array_key_exists($estado, $totales)) {
        $totales[$estado]++;
      }
      $totalHoras += $detalle['horas_inasistencia'];
    }

    $pdf = Pdf::loadView('asistencias.coordinador-estudiante-pdf', [
      'usuario' => $user,
      'generatedAt' => now(),
      'seccion' => $seccion,
      'estudiante' => $estudiante,
      'detalles' => $detalles,
      'totales' => $totales,
      'totalHoras' => $totalHoras,
      'startDate' => $startDate,
      'endDate' => $endDate,
      'filters' => [
        'start_date' => $startDate ? $startDate->toDateString() : null,
        'end_date' => $endDate ? $endDate->toDateString() : null,
        'week' => $weekValue,
        'reference_date' => $referenceDateValue,
      ],
    ])->setPaper('a4', 'portrait');

    $fileName = sprintf(
      'detalle_inasistencias_%s_%s.pdf',
      $estudiante->id,
      now()->format('Y-m-d_His')
    );

    return $pdf->stream($fileName);
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

    $dayKeys = [
      'monday',
      'tuesday',
      'wednesday',
      'thursday',
      'friday',
    ];

    $dayLabels = [
      'monday' => 'Lun',
      'tuesday' => 'Mar',
      'wednesday' => 'Mié',
      'thursday' => 'Jue',
      'friday' => 'Vie',
    ];

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

      $inasistenciasPorDia = AsistenciaEstudiante::select(
        'secciones.id as seccion_id',
        'estudiantes.id as estudiante_id',
        'asistencias.fecha'
      )
        ->join('asistencias', 'asistencias.id', '=', 'asistencia_estudiante.asistencia_id')
        ->join('horarios', 'horarios.id', '=', 'asistencias.horario_id')
        ->join('asignaciones', 'asignaciones.id', '=', 'horarios.asignacion_id')
        ->join('secciones', 'secciones.id', '=', 'asignaciones.seccion_id')
        ->join('estudiantes', 'estudiantes.id', '=', 'asistencia_estudiante.estudiante_id')
        ->where('asistencia_estudiante.estado', 'I')
        ->whereIn('secciones.id', $seccionIds)
        ->when($startDate, function ($query) use ($startDate) {
          $query->whereDate('asistencias.fecha', '>=', $startDate->toDateString());
        })
        ->when($endDate, function ($query) use ($endDate) {
          $query->whereDate('asistencias.fecha', '<=', $endDate->toDateString());
        })
        ->get()
        ->groupBy(function ($row) {
          return $row->seccion_id . '|' . $row->estudiante_id;
        })
        ->map(function ($rows) use ($dayKeys) {
          $flags = array_fill_keys($dayKeys, false);

          foreach ($rows as $row) {
            $fecha = $row->fecha instanceof Carbon ? $row->fecha : Carbon::parse($row->fecha);
            $dayKey = strtolower($fecha->format('l'));

            if (array_key_exists($dayKey, $flags)) {
              $flags[$dayKey] = true;
            }
          }

          return $flags;
        });

      $totalEstudiantes = $registros->count();

      foreach ($registros as $registro) {
        $count = (int) $registro->total_inasistencias;
        $valorDoble = $count * 2;
        $nombreEstudiante = trim($registro->nombres . ' ' . $registro->apellidos);
        $mapKey = $registro->seccion_id . '|' . $registro->estudiante_id;
        $diasInasistencia = $inasistenciasPorDia[$mapKey] ?? array_fill_keys($dayKeys, false);

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
          'dias_inasistencia' => $diasInasistencia,
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
      'dayLabels' => $dayLabels,
    ];
  }

  private function resolveDateRange(Request $request): array
  {
    $startDateInput = $request->input('start_date');
    $endDateInput = $request->input('end_date');
    $weekInput = $request->input('week');
    $referenceDateInput = $request->input('reference_date');

    $startDate = null;
    $endDate = null;

    if ($referenceDateInput) {
      try {
        $reference = Carbon::createFromFormat('Y-m-d', $referenceDateInput);
        $startDate = $reference->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = $reference->copy()->endOfWeek(Carbon::SUNDAY);
      } catch (\Throwable $exception) {
        $startDate = null;
        $endDate = null;
      }
    }

    if (!$startDate && !$endDate && $weekInput && preg_match('/^(\d{4})-W(\d{2})$/', $weekInput, $matches)) {
      try {
        $startDate = Carbon::now()->setISODate((int) $matches[1], (int) $matches[2])->startOfWeek(Carbon::MONDAY);
        $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY);
      } catch (\Throwable $exception) {
        $startDate = null;
        $endDate = null;
      }
    }

    try {
      if (!$startDate && $startDateInput) {
        $startDate = Carbon::createFromFormat('Y-m-d', $startDateInput)->startOfDay();
      }
    } catch (\Throwable $exception) {
      $startDate = null;
    }

    try {
      if (!$endDate && $endDateInput) {
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
      $startDate = $now->copy()->startOfWeek(Carbon::MONDAY);
      $endDate = $now->copy()->endOfWeek(Carbon::SUNDAY);
    }

    if ($startDate && !$endDate) {
      $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY);
    }

    if (!$startDate && $endDate) {
      $startDate = $endDate->copy()->startOfWeek(Carbon::MONDAY);
    }

    return [$startDate, $endDate];
  }
}
