@extends('adminlte::page')

@section('title', 'Resumen de Inasistencias')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Resumen de Inasistencias</h1>
        <a href="{{ route('asistencias.reporte') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Volver al reporte
        </a>
    </div>
    <hr class="mt-2 mb-4">
@endsection

@section('content')
<div class="container-fluid">
    @php
        $startValue = $filters['start_date'] ?? now()->toDateString();
        $endValue = $filters['end_date'] ?? now()->toDateString();
        $referenceValue = $filters['reference_date'] ?? null;
        $weekValue = $filters['week'] ?? ($startDate ? sprintf('%s-W%02d', $startDate->format('o'), (int) $startDate->format('W')) : null);
        $pdfParams = array_filter([
            'start_date' => $startValue,
            'end_date' => $endValue,
            'week' => $weekValue,
            'reference_date' => $referenceValue,
        ]);
    @endphp

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('asistencias.coordinador.index') }}">
                <div class="form-row align-items-end">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="start_date" class="font-weight-semibold">Desde</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startValue }}">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="end_date" class="font-weight-semibold">Hasta</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endValue }}">
                    </div>
                    <div class="col-md-4 col-sm-12 mb-3 d-flex">
                        <button type="submit" class="btn btn-primary mr-2 flex-fill">
                            <i class="fas fa-filter mr-1"></i>Aplicar filtro
                        </button>
                        <a href="{{ route('asistencias.coordinador.index') }}" class="btn btn-outline-secondary">
                            Limpiar
                        </a>
                    </div>
                </div>
                @if($startDate && $endDate)
                    <div class="text-muted small mt-1">
                        Mostrando registros del
                        <strong>{{ $startDate->format('d/m/Y') }}</strong>
                        @if(!$startDate->equalTo($endDate))
                            al <strong>{{ $endDate->format('d/m/Y') }}</strong>
                        @endif
                        @if($weekValue)
                            <span class="ml-1">(Semana {{ ltrim($startDate ? $startDate->format('W') : substr($weekValue, -2), '0') }} / {{ $startDate ? $startDate->format('o') : substr($weekValue, 0, 4) }})</span>
                        @endif
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-left border-danger shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="card-icon bg-danger text-white rounded-lg d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <div class="ml-3">
                            <h2 class="mb-0">{{ $totalInasistencias }}</h2>
                            <p class="mb-0 text-muted">Inasistencias Totales</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-left border-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="card-icon bg-warning text-white rounded-lg d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="ml-3">
                            <h2 class="mb-0">{{ $totalMultiplicado }}</h2>
                            <p class="mb-0 text-muted">Horas de inasistencia</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-12 mb-3">
            <div class="card border-left border-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="card-icon bg-primary text-white rounded-lg d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="ml-3">
                            <h2 class="mb-0">{{ $totalEstudiantes }}</h2>
                            <p class="mb-0 text-muted">Estudiantes con inasistencias</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title mb-1">
                    <i class="fas fa-layer-group text-primary mr-2"></i>
                    Secciones monitoreadas
                </h3>
                <span class="text-muted small">Haz clic en una sección para ver los estudiantes con inasistencias.</span>
                @if(($summaryMode ?? 'weekly') === 'monthly')
                    <span class="text-muted small d-block">Vista mensual activa: cada columna representa una semana del mes seleccionado.</span>
                @endif
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('asistencias.coordinador.pdf', $pdfParams) }}" class="btn btn-sm btn-primary mr-3" target="_blank">
                    <i class="fas fa-file-pdf mr-2"></i>Descargar PDF
                </a>
                <span class="badge badge-primary px-3 py-2">
                    {{ $totalSeccionesConInasistencias }} / {{ $totalSeccionesAsignadas }} con inasistencias
                </span>
            </div>
        </div>
        <div class="card-body">
            @if($sections->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="fas fa-info-circle mr-2"></i>
                    No tienes secciones asignadas actualmente.
                </div>
            @else
                <div class="accordion" id="seccionesAccordion">
                    @foreach($sections as $section)
                        <div class="card mb-2 accordion-card shadow-sm">
                            <div class="card-header p-0" id="heading-{{ $section['seccion_id'] }}">
                                <button class="accordion-toggle btn btn-link btn-block text-left px-3 py-3 d-flex justify-content-between align-items-center"
                                    type="button"
                                    data-toggle="collapse"
                                    data-target="#collapse-{{ $section['seccion_id'] }}"
                                    aria-expanded="false"
                                    aria-controls="collapse-{{ $section['seccion_id'] }}">
                                    <div>
                                        <span class="font-weight-bold text-dark">{{ $section['grado'] }} - {{ $section['seccion'] }}</span>
                                        <span class="text-muted d-block small">{{ count($section['estudiantes']) }} estudiante(s) con inasistencias</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-pill {{ $section['total_inasistencias'] > 0 ? 'badge-danger' : 'badge-secondary' }} mr-2 px-3 py-2">
                                            {{ $section['total_inasistencias'] }}
                                        </span>
                                        <span class="badge badge-pill {{ $section['total_multiplicado'] > 0 ? 'badge-warning' : 'badge-secondary' }} px-3 py-2">
                                            {{ $section['total_multiplicado'] }}
                                        </span>
                                    </div>
                                </button>
                            </div>
                            <div id="collapse-{{ $section['seccion_id'] }}" class="collapse" aria-labelledby="heading-{{ $section['seccion_id'] }}" data-parent="#seccionesAccordion">
                                <div class="card-body">
                                    @if(count($section['estudiantes']) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Estudiante</th>
                                                        <th class="text-center">Inasistencias</th>
                                                        <th class="text-center">Horas</th>
                                                        <th class="text-center">Detalle</th>
                                                        @foreach($dayLabels as $dayKey => $label)
                                                            <th class="text-center" title="{{ $label }}">{{ $label }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($section['estudiantes'] as $estudiante)
                                                        <tr>
                                                            <td class="font-weight-semibold">{{ $estudiante['estudiante'] }}</td>
                                                            <td class="text-center">
                                                                <span class="badge badge-pill {{ $estudiante['inasistencias'] > 0 ? 'badge-danger' : 'badge-secondary' }} px-3 py-2">
                                                                    {{ $estudiante['inasistencias'] }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge badge-pill {{ $estudiante['valor_doble'] > 0 ? 'badge-warning' : 'badge-secondary' }} px-3 py-2">
                                                                    {{ $estudiante['valor_doble'] }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <a href="{{ route('asistencias.coordinador.estudiante.pdf', array_merge(['seccion' => $section['seccion_id'], 'estudiante' => $estudiante['estudiante_id']], $pdfParams)) }}"
                                                                   class="btn btn-outline-primary btn-sm"
                                                                   target="_blank"
                                                                   title="Ver detalle semanal">
                                                                    <i class="fas fa-file-pdf"></i>
                                                                </a>
                                                            </td>
                                                            @foreach($dayLabels as $dayKey => $label)
                                                                @php
                                                                    $faltóDia = $estudiante['dias_inasistencia'][$dayKey] ?? false;
                                                                @endphp
                                                                <td class="text-center">
                                                                    @if($faltóDia)
                                                                        <span class="badge badge-danger" aria-label="Inasistencia el {{ $label }}">F</span>
                                                                    @else
                                                                        <span class="text-muted">—</span>
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-check-circle mr-2 text-success"></i>
                                            No se registran inasistencias para esta sección.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .card-icon {
        border-radius: 0.5rem;
        font-size: 1.4rem;
    }

    .card.border-left {
        border-left-width: 5px !important;
    }

    .badge-pill {
        font-size: 0.95rem;
    }

    .accordion-card {
        border: none;
    }

    .accordion-toggle {
        color: inherit;
        text-decoration: none;
        transition: background-color 0.2s ease-in-out;
    }

    .accordion-toggle:hover {
        text-decoration: none;
        background-color: #f8f9fa;
    }
</style>
@endsection
