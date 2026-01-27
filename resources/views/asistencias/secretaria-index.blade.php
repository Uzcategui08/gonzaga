@extends('adminlte::page')

@section('title', 'Reporte de Asistencia por Género')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Reporte de Asistencia por Género</h1>
        <a href="{{ route('asistencias.reporte') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Volver al registro
        </a>
    </div>
    <hr class="mt-2 mb-4">
@endsection

@section('content')
<div class="container-fluid">
    @php
        $startValue = $filters['start_date'] ?? '';
        $endValue = $filters['end_date'] ?? '';
        $nivelValue = $filters['nivel'] ?? '';
    @endphp

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('asistencias.secretaria.index') }}">
                <div class="form-row align-items-end">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="start_date" class="font-weight-semibold">Desde</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startValue }}">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="end_date" class="font-weight-semibold">Hasta</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endValue }}">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="nivel" class="font-weight-semibold">Nivel</label>
                        <select name="nivel" id="nivel" class="form-control">
                            <option value="">Todos</option>
                            <option value="secundaria" {{ $nivelValue === 'secundaria' ? 'selected' : '' }}>Secundaria</option>
                            <option value="primaria" {{ $nivelValue === 'primaria' ? 'selected' : '' }}>Primaria</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-12 mb-3 d-flex">
                        <button type="submit" class="btn btn-primary mr-2 flex-fill">
                            <i class="fas fa-filter mr-1"></i>Aplicar filtro
                        </button>
                        <a href="{{ route('asistencias.secretaria.index') }}" class="btn btn-outline-secondary">
                            Limpiar
                        </a>
                    </div>
                </div>
                @if($startDate || $endDate)
                    <div class="text-muted small">
                        Mostrando registros
                        @if($startDate)
                            desde <strong>{{ $startDate->format('d/m/Y') }}</strong>
                        @endif
                        @if($startDate && $endDate)
                            hasta
                        @elseif(!$startDate && $endDate)
                            hasta
                        @endif
                        @if($endDate)
                            <strong>{{ $endDate->format('d/m/Y') }}</strong>
                        @endif
                    </div>
                @else
                    <div class="text-muted small">Mostrando asistencia del mes en curso.</div>
                @endif
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-left border-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="card-icon bg-primary text-white rounded-lg d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-male"></i>
                        </div>
                        <div class="ml-3">
                            <h2 class="mb-0">{{ $totals['masculinos'] }}</h2>
                            <p class="mb-0 text-muted">Hombres asistentes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-left border-pink shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="card-icon bg-pink text-white rounded-lg d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-female"></i>
                        </div>
                        <div class="ml-3">
                            <h2 class="mb-0">{{ $totals['femeninos'] }}</h2>
                            <p class="mb-0 text-muted">Mujeres asistentes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-12 mb-3">
            <div class="card border-left border-success shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="card-icon bg-success text-white rounded-lg d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="ml-3">
                            <h2 class="mb-0">{{ $totals['total'] }}</h2>
                            <p class="mb-0 text-muted">Total asistentes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $exportQuery = array_filter([
            'start_date' => $filters['start_date'] ?? null,
            'end_date' => $filters['end_date'] ?? null,
            'nivel' => $filters['nivel'] ?? null,
        ]);
    @endphp

    <div class="card shadow-sm">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-table text-primary mr-2"></i>
                Detalle por sección
            </h3>
            <div class="btn-group" role="group" aria-label="Exportaciones">
                <a href="{{ route('asistencias.secretaria.pdf', $exportQuery) }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
                <a href="{{ route('asistencias.secretaria.excel', $exportQuery) }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Grado</th>
                            <th>Sección</th>
                            <th class="text-center">Hombres</th>
                            <th class="text-center">Mujeres</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sections as $section)
                            <tr>
                                <td>{{ $section['grado'] }}</td>
                                <td>{{ $section['seccion'] }}</td>
                                <td class="text-center">
                                    <span class="badge badge-pill {{ $section['masculinos'] > 0 ? 'badge-primary' : 'badge-secondary' }} px-3 py-2">
                                        {{ $section['masculinos'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill {{ $section['femeninos'] > 0 ? 'badge-pink' : 'badge-secondary' }} px-3 py-2">
                                        {{ $section['femeninos'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill {{ $section['total'] > 0 ? 'badge-success' : 'badge-secondary' }} px-3 py-2">
                                        {{ $section['total'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No se encontraron asistentes registrados en el periodo seleccionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($sections->isNotEmpty())
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-right">Totales</th>
                                <th class="text-center">{{ $totals['masculinos'] }}</th>
                                <th class="text-center">{{ $totals['femeninos'] }}</th>
                                <th class="text-center">{{ $totals['total'] }}</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
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

    .border-pink {
        border-color: #e83e8c !important;
    }

    .bg-pink {
        background-color: #e83e8c !important;
    }

    .badge-pink {
        background-color: #e83e8c !important;
        color: #fff;
    }
</style>
@endsection
