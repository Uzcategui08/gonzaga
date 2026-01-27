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

    // Por defecto: Secundaria (solo si el parámetro no viene en la URL).
    // Si el usuario selecciona "Todos", el formulario envía nivel="" y se respeta.
    $rawNivel = $request->has('nivel') ? $request->input('nivel') : 'secundaria';
    $nivel = $this->normalizeNivel($rawNivel);

    return view('asistencias.secretaria-index', [
      'sections' => $sectionsCollection,
      'totals' => $totals,
      'startDate' => $startDate,
      'endDate' => $endDate,
      'filters' => [
        'start_date' => $startDate ? $startDate->toDateString() : null,
        'end_date' => $endDate ? $endDate->toDateString() : null,
        'nivel' => $nivel,
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

    // Por defecto: Secundaria (solo si el parámetro no viene en la URL).
    $rawNivel = $request->has('nivel') ? $request->input('nivel') : 'secundaria';
    $nivel = $this->normalizeNivel($rawNivel);

    $seccionesQuery = Seccion::with(['grado:id,nombre,nivel'])
      ->orderBy('nombre');

    if ($nivel) {
      $seccionesQuery->whereHas('grado', fn($q) => $q->whereRaw('LOWER(nivel) = ?', [$nivel]));
    }

    $secciones = $seccionesQuery->get();

    $sectionsData = [];
    foreach ($secciones as $seccion) {
      $sectionsData[$seccion->id] = [
        'seccion_id' => $seccion->id,
        'grado' => optional($seccion->grado)->nombre ?? 'Sin grado',
        'nivel' => optional($seccion->grado)->nivel,
        'seccion' => $seccion->nombre,
        'masculinos' => 0,
        'femeninos' => 0,
        'total' => 0,
      ];
    }

    if (!empty($sectionsData)) {
      $attendanceRecords = AsistenciaEstudiante::select(
        'asistencia_estudiante.estudiante_id',
        'estudiantes.genero',
        'secciones.id as seccion_id',
        'secciones.nombre as seccion_nombre',
        'grados.nombre as grado_nombre',
        'grados.nivel as grado_nivel',
        'asistencias.fecha',
        'asistencias.hora_inicio'
      )
        ->join('asistencias', 'asistencias.id', '=', 'asistencia_estudiante.asistencia_id')
        ->join('horarios', 'horarios.id', '=', 'asistencias.horario_id')
        ->join('asignaciones', 'asignaciones.id', '=', 'horarios.asignacion_id')
        ->join('secciones', 'secciones.id', '=', 'asignaciones.seccion_id')
        ->leftJoin('grados', 'grados.id', '=', 'secciones.grado_id')
        ->join('estudiantes', 'estudiantes.id', '=', 'asistencia_estudiante.estudiante_id')
        ->whereIn('asistencia_estudiante.estado', ['A', 'P'])
        ->whereIn('secciones.id', array_keys($sectionsData))
        ->when($startDate, function ($query) use ($startDate) {
          $query->whereDate('asistencias.fecha', '>=', $startDate->toDateString());
        })
        ->when($endDate, function ($query) use ($endDate) {
          $query->whereDate('asistencias.fecha', '<=', $endDate->toDateString());
        })
        ->get();

      $sessionsBySection = [];

      foreach ($attendanceRecords as $record) {
        $sectionId = (int) $record->seccion_id;

        if (!isset($sectionsData[$sectionId])) {
          $sectionsData[$sectionId] = [
            'seccion_id' => $sectionId,
            'grado' => $record->grado_nombre ?? 'Sin grado',
            'nivel' => $record->grado_nivel,
            'seccion' => $record->seccion_nombre,
            'masculinos' => 0,
            'femeninos' => 0,
            'total' => 0,
          ];
        }

        $fechaValue = $record->fecha instanceof Carbon
          ? $record->fecha->toDateString()
          : (string) $record->fecha;

        $horaValue = $record->hora_inicio ? (string) $record->hora_inicio : '00:00:00';
        $sessionKey = $fechaValue . '|' . $horaValue;
        $studentId = (int) $record->estudiante_id;
        $normalizedGender = $this->normalizeGenero($record->genero);

        if (!isset($sessionsBySection[$sectionId])) {
          $sessionsBySection[$sectionId] = [];
        }

        if (!isset($sessionsBySection[$sectionId][$sessionKey])) {
          $sessionsBySection[$sectionId][$sessionKey] = [
            'fecha' => $fechaValue,
            'hora' => $horaValue,
            'students' => [],
          ];
        }

        if (!isset($sessionsBySection[$sectionId][$sessionKey]['students'][$studentId])) {
          $sessionsBySection[$sectionId][$sessionKey]['students'][$studentId] = $normalizedGender;
        }
      }

      foreach ($sessionsBySection as $sectionId => $sessions) {
        $bestSession = null;
        $bestTotal = 0;

        foreach ($sessions as $sessionData) {
          $masculinos = 0;
          $femeninos = 0;

          foreach ($sessionData['students'] as $gender) {
            if ($gender === 'masculino') {
              $masculinos++;
            } elseif ($gender === 'femenino') {
              $femeninos++;
            }
          }

          $total = $masculinos + $femeninos;

          if ($total > $bestTotal) {
            $bestTotal = $total;
            $bestSession = [
              'masculinos' => $masculinos,
              'femeninos' => $femeninos,
              'total' => $total,
              'fecha' => $sessionData['fecha'],
              'hora' => $sessionData['hora'],
            ];
          } elseif ($total === $bestTotal && $bestSession !== null) {
            $currentDateTime = $this->makeSessionDateTime($sessionData['fecha'], $sessionData['hora']);
            $bestDateTime = $this->makeSessionDateTime($bestSession['fecha'], $bestSession['hora']);

            $shouldReplace = false;

            if ($currentDateTime && $bestDateTime) {
              $shouldReplace = $currentDateTime->greaterThan($bestDateTime);
            } elseif ($currentDateTime && !$bestDateTime) {
              $shouldReplace = true;
            }

            if ($shouldReplace) {
              $bestSession = [
                'masculinos' => $masculinos,
                'femeninos' => $femeninos,
                'total' => $total,
                'fecha' => $sessionData['fecha'],
                'hora' => $sessionData['hora'],
              ];
            }
          }
        }

        if ($bestSession !== null && isset($sectionsData[$sectionId])) {
          $sectionsData[$sectionId]['masculinos'] = $bestSession['masculinos'];
          $sectionsData[$sectionId]['femeninos'] = $bestSession['femeninos'];
          $sectionsData[$sectionId]['total'] = $bestSession['total'];
        }
      }
    }

    $sectionsCollection = collect($sectionsData)
      ->filter(function ($section) {
        return $section['total'] > 0;
      })
      ->sortBy(function ($section) {
        $nivelValue = $this->normalizeNivel($section['nivel'] ?? null);
        // Secundaria primero, luego Primaria, luego lo demás.
        $nivelOrder = match ($nivelValue) {
          'secundaria' => 0,
          'primaria' => 1,
          default => 2,
        };

        return sprintf('%d_%s_%s', $nivelOrder, $section['grado'], $section['seccion']);
      }, SORT_NATURAL | SORT_FLAG_CASE)
      ->values();

    $totals = [
      'masculinos' => $sectionsCollection->sum('masculinos'),
      'femeninos' => $sectionsCollection->sum('femeninos'),
    ];
    $totals['total'] = $totals['masculinos'] + $totals['femeninos'];

    return [$startDate, $endDate, $sectionsCollection, $totals];
  }

  private function normalizeNivel($nivel): ?string
  {
    if ($nivel === null) {
      return null;
    }

    $value = Str::lower(trim((string) $nivel));
    return in_array($value, ['primaria', 'secundaria'], true) ? $value : null;
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

  private function makeSessionDateTime(?string $date, ?string $time): ?Carbon
  {
    if (empty($date)) {
      return null;
    }

    $timeValue = $time ?: '00:00:00';

    try {
      return Carbon::parse(trim($date . ' ' . $timeValue));
    } catch (\Exception $ex) {
      return null;
    }
  }
}
