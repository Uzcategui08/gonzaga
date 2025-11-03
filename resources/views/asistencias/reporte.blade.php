@extends('adminlte::page')

@section('title', 'Reporte de Asistencias')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">
            Reporte de Asistencias
        </h1>
        <form method="GET" action="" class="mb-0">
            <div class="d-flex align-items-end flex-wrap">
                <div class="mr-2 mb-2">
                    <label for="fecha_desde" class="font-weight-bold mb-0">Desde</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" value="{{ $selectedStartDate }}">
                </div>
                <div class="mr-2 mb-2">
                    <label for="fecha_hasta" class="font-weight-bold mb-0">Hasta</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" value="{{ $selectedEndDate }}">
                </div>
                <div class="mr-2 mb-2">
                    <label for="flag" class="font-weight-bold mb-0">Marca</label>
                    <select name="flag" id="flag" class="form-control">
                        <option value="">Todas</option>
                        @foreach($flagOptions as $value => $label)
                            <option value="{{ $value }}" {{ ($selectedFlag ?? null) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </div>
        </form>
    </div>

@stop

@section('content')
<div class="container-fluid px-0">
    @php
        $flagOptions = $flagOptions ?? [];
        $selectedFlag = $selectedFlag ?? null;
    $selectedStartDate = $selectedStartDate ?? request('fecha_desde', request('fecha', now()->format('Y-m-d')));
    $selectedEndDate = $selectedEndDate ?? request('fecha_hasta', request('fecha', now()->format('Y-m-d')));
    @endphp
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-list text-primary mr-2"></i>Listado
            </h3>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive table-responsive-lg" style="margin: 0.5rem;">
                <table class="table table-hover table-striped datatable" id="asistencias-table">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th class="py-3">Registro</th>
                            <th class="py-3">Fecha Clase</th>
                            <th class="py-3">Hora</th>
                            <th class="py-3">Materia</th>
                            <th class="py-3">Profesor</th>
                            <th class="py-3">Contenido</th>
                            <th class="py-3">Observación General</th>
                            <th class="py-3">Resumen</th>
                            <th class="py-3">Acciones</th>
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
                                    {{ $asistencia->horario->asignacion->materia->nombre ?? 'N/A' }}
                                    @if($asistencia->horario->asignacion->seccion)
                                        <br>
                                        <small class="text-muted">
                                            {{ $asistencia->horario->asignacion->seccion->grado->nombre }} - {{ $asistencia->horario->asignacion->seccion->nombre }}
                                        </small>
                                    @endif
                                </span>
                            </td>
                            <td class="align-middle">
                                @php
                                    $profesor = $asistencia->profesor;
                                    $profesorName = $profesor ? ($profesor->user ? $profesor->user->name : 'N/A') : 'N/A';
                                @endphp
                                {{ $profesorName }}
                            </td>
                            <td class="text-wrap align-middle">
                                {{ $asistencia->contenido_clase ?? 'N/A' }}
                            </td>
                            <td class="text-wrap align-middle">
                                {{ $asistencia->observacion_general ?? 'N/A' }}
                            </td>
                            <td class="text-center align-middle">
                                @if($asistencia->estudiantes && $asistencia->estudiantes->count() > 0)
                                    @php
                                        $asistentes = $asistencia->estudiantes->whereIn('estado', ['A', 'P'])->count();
                                        $inasistentes = $asistencia->estudiantes->where('estado', 'I')->count();
                                        $pases = $asistencia->estudiantes->where('estado', 'P')->count();
                                    @endphp
                                    @php
                                        $masculinos = $asistencia->estudiantes->filter(function($ae) {
                                            return in_array($ae->estado, ['A', 'P']) && optional($ae->estudiante)->genero === 'M';
                                        })->count();
                                        $femeninos = $asistencia->estudiantes->filter(function($ae) {
                                            return in_array($ae->estado, ['A', 'P']) && optional($ae->estudiante)->genero === 'F';
                                        })->count();
                                    @endphp
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="d-flex justify-content-center mb-1">
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
                                        <div class="d-flex justify-content-center">
                                            <div class="mr-2">
                                                <span class="badge badge-pill py-2 px-3 badge-secondary" title="Masculinos">
                                                    <i class="fas fa-mars mr-1" style="color:#80bdff"></i>M: {{ $masculinos }}
                                                </span>
                                            </div>
                                            <div class="mr-2">
                                                <span class="badge badge-pill py-2 px-3 badge-secondary" title="Femeninos">
                                                    <i class="fas fa-venus mr-1" style="color:#ff80ee"></i>F: {{ $femeninos }}
                                                </span>
                                            </div>
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