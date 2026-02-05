@extends('adminlte::page')

@section('title', 'Consulta — Clase Extracurricular')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark">Consulta — Clase Extracurricular</h1>
        <div class="text-muted">{{ $clase->nombre }}</div>
    </div>
    <a href="{{ route('extracurricular.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
@php
    $diasSemana = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];
@endphp

<div class="container-fluid px-0">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="row">
                <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                    <div class="small text-muted">Profesor extracurricular</div>
                    <div class="font-weight-bold">{{ $clase->profesor?->user?->name ?? '—' }}</div>

                    <div class="mt-3 small text-muted">Día</div>
                    <div class="font-weight-bold">{{ $diasSemana[$clase->dia_semana] ?? '—' }}</div>

                    <div class="mt-3 small text-muted">Horario</div>
                    <div class="font-weight-bold">
                        @if(!empty($clase->hora_inicio) && !empty($clase->hora_fin))
                            {{ $clase->hora_inicio }} - {{ $clase->hora_fin }}
                        @else
                            —
                        @endif
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="small text-muted">Aula</div>
                    <div class="font-weight-bold">{{ $clase->aula ?? '—' }}</div>

                    <div class="mt-3 small text-muted">Descripción</div>
                    <div style="white-space: pre-wrap;">{{ $clase->descripcion ?? '—' }}</div>

                    <div class="mt-3 small text-muted">Total estudiantes</div>
                    <div class="font-weight-bold">{{ ($estudiantes ?? collect())->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-users text-primary mr-2"></i> Estudiantes de la clase
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="60">#</th>
                            <th>Estudiante</th>
                            <th>Sección</th>
                            <th>Código</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($estudiantes ?? collect()) as $e)
                            @php
                                $seccionLabel = $e->seccion?->grado?->nombre && $e->seccion?->nombre
                                    ? ($e->seccion->grado->nombre . ' - ' . $e->seccion->nombre)
                                    : '—';
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $e->apellidos }} {{ $e->nombres }}</div>
                                </td>
                                <td class="text-muted">{{ $seccionLabel }}</td>
                                <td class="text-muted">{{ $e->codigo_estudiante ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No hay estudiantes asignados a esta clase</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
