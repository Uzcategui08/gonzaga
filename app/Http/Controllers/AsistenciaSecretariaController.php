<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesAttendanceDateRange;
use App\Models\Asistencia;
use App\Models\AsistenciaEstudiante;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Exports\AsistenciaSecretariaExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class AsistenciaSecretariaController extends Controller
{
  use ResolvesAttendanceDateRange;

  public function index(Request $request)
  {
    $this->authorizeReportAccess();

    [$startDate, $endDate, $sectionsCollection, $totals] = $this->buildReport($request);

    return view('asistencias.secretaria-index', [
      'sections' => $sectionsCollection,
      'totals' => $totals,
      'startDate' => $startDate,
      'endDate' => $endDate,
      'filters' => [
        'start_date' => $startDate ? $startDate->toDateString() : null,
        'end_date' => $endDate ? $endDate->toDateString() : null,
      ],
    ]);
  }

  public function exportPdf(Request $request)
  {
    $this->authorizeReportAccess();

    [$startDate, $endDate, $sectionsCollection, $totals] = $this->buildReport($request);

    $pdf = Pdf::loadView('asistencias.secretaria-pdf', [
      'sections' => $sectionsCollection,
      'totals' => $totals,
      'startDate' => $startDate,
      'endDate' => $endDate,
      'generatedAt' => Carbon::now('America/Caracas'),
      'usuario' => Auth::user(),
    ])->setPaper('a4', 'portrait');

    return $pdf->download('reporte-asistencia-genero-' . now()->format('Ymd_His') . '.pdf');
  }

  public function exportExcel(Request $request)
  {
    $this->authorizeReportAccess();

    [$startDate, $endDate, $sectionsCollection, $totals] = $this->buildReport($request);

    $filename = 'reporte-asistencia-genero-' . now()->format('Ymd_His') . '.xlsx';

    return Excel::download(new AsistenciaSecretariaExport($sectionsCollection, $totals), $filename);
  }

  private function buildReport(Request $request): array
  {
    [$startDate, $endDate] = $this->resolveDateRange($request);

    $secciones = Seccion::with(['grado:id,nombre'])
      ->orderBy('nombre')
      ->get();

    $sectionsData = [];
    foreach ($secciones as $seccion) {
      $sectionsData[$seccion->id] = [
        'seccion_id' => $seccion->id,
        'grado' => optional($seccion->grado)->nombre ?? 'Sin grado',
        'seccion' => $seccion->nombre,
        'masculinos' => 0,
        'femeninos' => 0,
        'total' => 0,
      ];
    }

    if (!empty($sectionsData)) {
      $latestAsistenciasPorSeccion = Asistencia::select(
        'asistencias.id',
        'asistencias.fecha',
        'asistencias.hora_inicio',
        'asistencias.created_at',
        'secciones.id as seccion_id'
      )
        ->join('horarios', 'horarios.id', '=', 'asistencias.horario_id')
        ->join('asignaciones', 'asignaciones.id', '=', 'horarios.asignacion_id')
        ->join('secciones', 'secciones.id', '=', 'asignaciones.seccion_id')
        ->whereIn('secciones.id', array_keys($sectionsData))
        ->when($startDate, function ($query) use ($startDate) {
          $query->whereDate('asistencias.fecha', '>=', $startDate->toDateString());
        })
        ->when($endDate, function ($query) use ($endDate) {
          $query->whereDate('asistencias.fecha', '<=', $endDate->toDateString());
        })
        ->orderBy('asistencias.fecha', 'desc')
        ->orderBy('asistencias.hora_inicio', 'desc')
        ->orderBy('asistencias.created_at', 'desc')
        ->get()
        ->groupBy('seccion_id')
        ->map(function ($items) {
          return optional($items->first())->id;
        })
        ->filter();

      $asistenciaIds = $latestAsistenciasPorSeccion->values();

      if ($asistenciaIds->isNotEmpty()) {
        $registros = AsistenciaEstudiante::selectRaw(
          'secciones.id as seccion_id, ' .
            'secciones.nombre as seccion_nombre, ' .
            'grados.nombre as grado_nombre, ' .
            'estudiantes.genero, ' .
            'COUNT(*) as total_registros'
        )
          ->join('asistencias', 'asistencias.id', '=', 'asistencia_estudiante.asistencia_id')
          ->join('horarios', 'horarios.id', '=', 'asistencias.horario_id')
          ->join('asignaciones', 'asignaciones.id', '=', 'horarios.asignacion_id')
          ->join('secciones', 'secciones.id', '=', 'asignaciones.seccion_id')
          ->leftJoin('grados', 'grados.id', '=', 'secciones.grado_id')
          ->join('estudiantes', 'estudiantes.id', '=', 'asistencia_estudiante.estudiante_id')
          ->whereIn('asistencia_estudiante.estado', ['A', 'P'])
          ->whereIn('secciones.id', array_keys($sectionsData))
          ->whereIn('asistencia_estudiante.asistencia_id', $asistenciaIds)
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
            'estudiantes.genero'
          )
          ->orderBy('grados.nombre')
          ->orderBy('secciones.nombre')
          ->orderBy('estudiantes.genero')
          ->get();

        $registros->groupBy('seccion_id')->each(function ($registrosSeccion, $seccionId) use (&$sectionsData) {
          $registroReferencia = $registrosSeccion->first();

          if (!isset($sectionsData[$seccionId])) {
            $sectionsData[$seccionId] = [
              'seccion_id' => $seccionId,
              'grado' => $registroReferencia->grado_nombre ?? 'Sin grado',
              'seccion' => $registroReferencia->seccion_nombre,
              'masculinos' => 0,
              'femeninos' => 0,
              'total' => 0,
            ];
          }

          $contadores = $registrosSeccion
            ->reduce(function (array $carry, $registro) {
              $generoNormalizado = $this->normalizeGenero($registro->genero);

              if ($generoNormalizado === 'masculino') {
                $carry['masculinos'] += (int) $registro->total_registros;
              } elseif ($generoNormalizado === 'femenino') {
                $carry['femeninos'] += (int) $registro->total_registros;
              }

              return $carry;
            }, ['masculinos' => 0, 'femeninos' => 0]);

          $sectionsData[$seccionId]['masculinos'] = $contadores['masculinos'];
          $sectionsData[$seccionId]['femeninos'] = $contadores['femeninos'];
          $sectionsData[$seccionId]['total'] = $contadores['masculinos'] + $contadores['femeninos'];
        });
      }
    }

    $sectionsCollection = collect($sectionsData)
      ->filter(function ($section) {
        return $section['total'] > 0;
      })
      ->sortBy(function ($section) {
        return sprintf('%s_%s', $section['grado'], $section['seccion']);
      }, SORT_NATURAL | SORT_FLAG_CASE)
      ->values();

    $totals = [
      'masculinos' => $sectionsCollection->sum('masculinos'),
      'femeninos' => $sectionsCollection->sum('femeninos'),
    ];
    $totals['total'] = $totals['masculinos'] + $totals['femeninos'];

    return [$startDate, $endDate, $sectionsCollection, $totals];
  }

  private function authorizeReportAccess(): void
  {
    /** @var User|null $user */
    $user = Auth::user();

    $canSeeReport = $user
      && method_exists($user, 'hasRole')
      && ($user->hasRole('secretaria') || $user->hasRole('admin') || $user->hasRole('coordinador'));

    if (!$canSeeReport) {
      abort(403, 'Acceso no autorizado');
    }
  }

  private function normalizeGenero($genero): ?string
  {
    if ($genero === null) {
      return null;
    }

    $valor = Str::lower(trim((string) $genero));

    $mapa = [
      'm' => 'masculino',
      'h' => 'masculino',
      'masculino' => 'masculino',
      'male' => 'masculino',
      'hombre' => 'masculino',
      'f' => 'femenino',
      'femenino' => 'femenino',
      'female' => 'femenino',
      'mujer' => 'femenino',
    ];

    return $mapa[$valor] ?? null;
  }
}
