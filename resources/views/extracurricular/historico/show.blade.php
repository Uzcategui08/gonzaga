@extends('adminlte::page')

@section('title', 'Detalle Asistencia Extracurricular')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark">Detalle Asistencia</h1>
        <div class="text-muted">
            {{ $asistencia->clase?->nombre ?? 'Clase' }} — {{ optional($asistencia->fecha)->format('d/m/Y') }}
        </div>
    </div>
    <a href="{{ route('extracurricular.historico.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap" style="gap: .5rem;">
                        <span class="badge badge-primary badge-pill">Total: {{ $conteos['total'] ?? 0 }}</span>
                        <span class="badge badge-success badge-pill">Presentes: {{ $conteos['presentes'] ?? 0 }}</span>
                        <span class="badge badge-warning badge-pill">Pases: {{ $conteos['pases'] ?? 0 }}</span>
                        <span class="badge badge-danger badge-pill">Inasistencias: {{ $conteos['inasistencias'] ?? 0 }}</span>
                    </div>

                    <hr>

                    <div class="mb-2">
                        <div class="text-muted small">Profesor</div>
                        <div class="font-weight-bold">{{ $asistencia->clase?->profesor?->user?->name ?? '—' }}</div>
                    </div>

                    <div class="mb-2">
                        <div class="text-muted small">Contenido de la clase</div>
                        <div style="white-space: pre-wrap;">{{ $asistencia->contenido_clase }}</div>
                    </div>

                    @if(!empty($asistencia->observacion_general))
                        <div class="mb-2">
                            <div class="text-muted small">Observación general</div>
                            <div style="white-space: pre-wrap;">{{ $asistencia->observacion_general }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h3 class="card-title mb-0">Estudiantes</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th width="35%">Estudiante</th>
                                    <th width="15%">Estado</th>
                                    <th width="50%">Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($asistencia->estudiantes ?? collect()) as $estudiante)
                                    @php
                                        $estado = $estudiante->pivot?->estado;
                                        $obs = $estudiante->pivot?->observacion_individual;
                                        $seccionLabel = $estudiante->seccion?->grado?->nombre && $estudiante->seccion?->nombre
                                            ? ($estudiante->seccion->grado->nombre . ' - ' . $estudiante->seccion->nombre)
                                            : null;

                                        $estadoLabel = $estado === 'A' ? 'Asistente' : ($estado === 'P' ? 'Pase' : 'Inasistente');
                                        $estadoBadge = $estado === 'A' ? 'success' : ($estado === 'P' ? 'warning' : 'danger');
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $loop->iteration }}. {{ $estudiante->apellidos }} {{ $estudiante->nombres }}</strong>
                                            @if($seccionLabel)
                                                <div class="text-muted small">{{ $seccionLabel }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $estadoBadge }}">{{ $estadoLabel }}</span>
                                        </td>
                                        <td>
                                            <div style="white-space: pre-wrap;">{{ $obs }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
