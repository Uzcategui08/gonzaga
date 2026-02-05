@extends('adminlte::page')

@section('title', 'Histórico Extracurricular')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark">Histórico Extracurricular</h1>
        <div class="text-muted">Asistencias registradas (clases pasadas)</div>
    </div>
    <a href="{{ route('extracurricular.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('extracurricular.historico.index') }}">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="desde" class="font-weight-bold">Desde</label>
                        <input type="date" id="desde" name="desde" value="{{ old('desde', $filtros['desde'] ?? '') }}" class="form-control @error('desde') is-invalid @enderror">
                        @error('desde')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label for="hasta" class="font-weight-bold">Hasta</label>
                        <input type="date" id="hasta" name="hasta" value="{{ old('hasta', $filtros['hasta'] ?? '') }}" class="form-control @error('hasta') is-invalid @enderror">
                        @error('hasta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label for="clase_id" class="font-weight-bold">Clase</label>
                        <select id="clase_id" name="clase_id" class="form-control @error('clase_id') is-invalid @enderror">
                            <option value="">Todas</option>
                            @foreach(($clasesParaFiltro ?? collect()) as $clase)
                                <option value="{{ $clase->id }}" {{ (string) old('clase_id', $filtros['clase_id'] ?? '') === (string) $clase->id ? 'selected' : '' }}>
                                    {{ $clase->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('clase_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Clase</th>
                            <th>Profesor</th>
                            <th class="text-center">Presentes</th>
                            <th class="text-center">Pases</th>
                            <th class="text-center">Inasist.</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($asistencias ?? []) as $asistencia)
                            @php
                                $stats = ($statsPorAsistencia[$asistencia->id] ?? null);
                                $clase = $asistencia->clase;
                                $profesorNombre = $clase?->profesor?->user?->name;
                            @endphp
                            <tr>
                                <td>{{ optional($asistencia->fecha)->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $clase?->nombre ?? '—' }}</strong>
                                    @if(!empty($clase?->hora_inicio) && !empty($clase?->hora_fin))
                                        <div class="text-muted small">{{ $clase->hora_inicio }} - {{ $clase->hora_fin }}</div>
                                    @endif
                                </td>
                                <td>{{ $profesorNombre ?? '—' }}</td>
                                <td class="text-center"><span class="badge badge-success badge-pill">{{ $stats['presentes'] ?? 0 }}</span></td>
                                <td class="text-center"><span class="badge badge-warning badge-pill">{{ $stats['pases'] ?? 0 }}</span></td>
                                <td class="text-center"><span class="badge badge-danger badge-pill">{{ $stats['inasistencias'] ?? 0 }}</span></td>
                                <td class="text-right">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('extracurricular.historico.show', $asistencia->id) }}">
                                        <i class="fas fa-eye mr-1"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No hay asistencias registradas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(($asistencias ?? null) && method_exists($asistencias, 'links'))
            <div class="card-footer">
                {{ $asistencias->links() }}
            </div>
        @endif
    </div>
</div>
@stop
