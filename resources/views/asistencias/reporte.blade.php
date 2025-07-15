@extends('adminlte::page')

@section('title', 'Reporte de Asistencias')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">
            Reporte de Asistencias
        </h1>
        <!--
        <div class="btn-group">
            <a href="{{ route('asistencias.reporte-pdf') }}" 
               class="btn btn-primary" 
               target="_blank">
                <i class="fas fa-file-pdf mr-2"></i>Generar PDF
            </a>
        </div>
        -->
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
            <div  style="margin: 0.5rem; overflow-x: auto;">
                <table class="table table-hover table-striped datatable" style="width:100%" id="asistencias-table">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th >Registro</th>
                            <th >Fecha Clase</th>
                            <th >Hora</th>
                            <th >Materia</th>
                            <th >Profesor</th>
                            <th >Contenido</th>
                            <th >Resumen</th>
                            <th >Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asistencias as $asistencia)
                        <tr class="border-bottom">
                            <td></td>
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
                            <td data-title="Contenido">
    {{ $asistencia->contenido_clase ?? 'N/A' }}
</td>
                            <td class="text-center align-middle">
                                @if($asistencia->estudiantes && $asistencia->estudiantes->count() > 0)
                                    @php
                                        $asistentes = $asistencia->estudiantes->where('estado', 'A')->count();
                                        $inasistentes = $asistencia->estudiantes->where('estado', 'I')->count();
                                        $pases = $asistencia->estudiantes->where('estado', 'P')->count();
                                    @endphp
                                    <div class="d-flex justify-content-center">
                                        <div class="mr-2">
                                            <span class="badge badge-pill py-2 px-3 badge-success" title="Asistentes">
                                                <i class="fas fa-user-check mr-1"></i>{{ $asistentes }}
                                            </span>
                                        </div>
                                        <div class="mr-2">
                                            <span class="badge badge-pill py-2 px-3 badge-danger" title="Inasistentes">
                                                <i class="fas fa-user-times mr-1"></i>{{ $inasistentes }}
                                            </span>
                                        </div>
                                        <div class="mr-2">
                                            <span class="badge badge-pill py-2 px-3 badge-info" title="Pases">
                                                <i class="fas fa-user-tag mr-1"></i>{{ $pases }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Sin datos</span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center">
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