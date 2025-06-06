@extends('adminlte::page')

@section('title', 'Reporte de Asistencias')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">
            Reporte de Asistencias
        </h1>
        <div class="btn-group">
            <a href="{{ route('asistencias.reporte-pdf') }}" 
               class="btn btn-primary" 
               target="_blank">
                <i class="fas fa-file-pdf mr-2"></i>Generar PDF
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-list text-primary mr-2"></i>Listado
            </h3>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive-md rounded-lg" style="margin: 0.5rem;">
                <table class="table table-hover mb-0 datatable" id="asistencias-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-nowrap py-3" style="width: 10%">Registro</th>
                            <th class="text-nowrap py-3" style="width: 10%">Fecha Clase</th>
                            <th class="text-nowrap py-3" style="width: 5%">Hora</th>
                            <th class="py-3" style="width: 15%">Materia</th>
                            <th class="py-3" style="width: 15%">Profesor</th>
                            <th class="py-3" style="width: 20%">Contenido</th>
                            <th class="py-3" style="width: 20%">Observaciones</th>
                            <th class="text-nowrap py-3" style="width: 10%">Resumen</th>
                            <th class="text-nowrap py-3" style="width: 10%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asistencias as $asistencia)
                        <tr class="border-bottom">
                            <td class="text-nowrap align-middle">{{ $asistencia->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-nowrap align-middle">{{ $asistencia->fecha ? $asistencia->fecha->format('d/m/Y') : 'N/A' }}</td>
                            <td class="text-nowrap align-middle">{{ $asistencia->hora_inicio ?? 'N/A' }}</td>
                            <td class="align-middle">
                                <span class="font-weight-semibold text-dark">
                                    {{ $asistencia->materia ? $asistencia->materia->nombre : 'N/A' }}
                                    @if($asistencia->seccion)
                                        <br>
                                        <small class="text-muted">
                                            {{ $asistencia->seccion->nombre }} - {{ $asistencia->seccion->grado->nombre }}
                                        </small>
                                    @endif
                                </span>
                            </td>
                            <td class="align-middle">
                                @php
                                    $profesor = $asistencia->profesor;
                                    $profesorName = $profesor ? ($profesor->user ? $profesor->user->name : 'N/A') : 'N/A';
                                @endphp
                                <span class="font-weight-semibold text-dark">{{ $profesorName }}</span>
                                @if(!$profesor)
                                    <br><small class="text-danger">(ID: {{ $asistencia->profesor_id }})</small>
                                @endif
                            </td>
                            <td class="align-middle">
                                <span class="text-truncate" style="max-width: 200px;" title="{{ $asistencia->contenido_clase ?? '' }}">
                                    {{ $asistencia->contenido_clase ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="align-middle">
                                <span class="text-truncate" style="max-width: 200px;" title="{{ $asistencia->observacion_general ?? '' }}">
                                    {{ $asistencia->observacion_general ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="text-center align-middle">
                                @if($asistencia->estudiantes && $asistencia->estudiantes->count() > 0)
                                    @php
                                        $presentes = $asistencia->estudiantes->where('estado', 'P')->count();
                                        $ausentes = $asistencia->estudiantes->where('estado', 'A')->count();
                                        $tardios = $asistencia->estudiantes->where('estado', 'I')->count();
                                    @endphp
                                    <div class="d-flex justify-content-center">
                                        <div class="mr-2">
                                            <span class="badge badge-pill py-2 px-3 badge-success" title="Presentes">
                                                <i class="fas fa-user-check mr-1"></i>{{ $presentes }}
                                            </span>
                                        </div>
                                        <div class="mr-2">
                                            <span class="badge badge-pill py-2 px-3 badge-danger" title="Ausentes">
                                                <i class="fas fa-user-times mr-1"></i>{{ $ausentes }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="badge badge-pill py-2 px-3 badge-warning" title="Tardíos">
                                                <i class="fas fa-user-clock mr-1"></i>{{ $tardios }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Sin datos</span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('asistencias.edit', $asistencia->id) }}" 
                                       class="btn btn-sm btn-light mx-1 border"
                                       data-toggle="tooltip" 
                                       title="Editar">
                                        <i class="fas fa-edit text-warning"></i>
                                    </a>
                                    <a href="{{ route('asistencias.generate-pdf', $asistencia->id) }}" 
                                       class="btn btn-sm btn-light mx-1 border"
                                       target="_blank"
                                       data-toggle="tooltip" 
                                       title="Generar PDF">
                                        <i class="fas fa-file-pdf text-info"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .table thead th {
        vertical-align: middle;
        font-weight: bold;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .text-truncate {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .datatable {
        font-size: 0.9rem;
    }
    
    .badge {
        font-size: 0.8em;
        padding: 0.4em 0.6em;
    }
    
    .badge i {
        font-size: 0.8em;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            border: 0;
        }
        .table thead {
            display: none;
        }
        .table tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
        }
        .table td {
            display: block;
            text-align: right;
            padding-left: 50%;
            position: relative;
            border-bottom: 1px solid #dee2e6;
        }
        .table td::before {
            content: attr(data-label);
            position: absolute;
            left: 1rem;
            width: calc(50% - 1rem);
            padding-right: 1rem;
            font-weight: bold;
            text-align: left;
        }
        .table td:last-child {
            border-bottom: 0;
        }
    }
</style>
@stop