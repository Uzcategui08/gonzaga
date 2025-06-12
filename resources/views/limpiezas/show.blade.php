@extends('adminlte::page')

@section('title', 'Detalles de Limpieza')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Detalles</h1>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body p-4">
            <div class="mb-4">
                <h5 class="font-weight-semibold text-muted mb-3">
                    Información de la Clase
                </h5>
                <div class="p-3 bg-light rounded">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-white text-dark border mr-2">
                            <i class="far fa-clock text-primary mr-1"></i>{{ \Carbon\Carbon::parse($limpieza->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($limpieza->hora_fin)->format('H:i') }}
                        </span>
                        <span class="badge bg-white text-dark border mr-2">
                            <i class="fas fa-book text-success mr-1"></i>{{ optional($limpieza->horario)?->asignacion?->materia?->nombre ?? 'N/A' }}
                        </span>
                        <span class="badge bg-white text-dark border mr-2">
                            <i class="fas fa-users text-warning mr-1"></i>{{ optional($limpieza->horario)?->asignacion?->seccion?->nombre ?? 'N/A' }}
                        </span>
                        <span class="badge bg-white text-dark border mr-2">
                            <i class="fas fa-door-open text-secondary mr-1"></i>{{ optional($limpieza->horario)->aula ?? 'N/A' }}
                        </span>
                        <span class="badge bg-white text-dark border">
                            <i class="fas fa-chalkboard-teacher text-info mr-1"></i>{{ $limpieza->profesor->usuario->name ?? 'Sin profesor' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h5 class="font-weight-semibold text-muted mb-3">
                    Detalles de la Limpieza
                </h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold text-gray-700">Fecha</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-calendar text-muted"></i>
                                </span>
                            </div>
                            <input type="date" 
                                   class="form-control form-control-lg" 
                                   value="{{ \Carbon\Carbon::parse($limpieza->fecha)->format('Y-m-d') }}"
                                   readonly>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold text-gray-700">Hora Inicio</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-clock text-muted"></i>
                                </span>
                            </div>
                            <input type="time" 
                                   class="form-control form-control-lg" 
                                   value="{{ \Carbon\Carbon::parse($limpieza->hora_inicio)->format('H:i') }}"
                                   readonly>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold text-gray-700">Hora Fin</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-clock text-muted"></i>
                                </span>
                            </div>
                            <input type="time" 
                                   class="form-control form-control-lg" 
                                   value="{{ \Carbon\Carbon::parse($limpieza->hora_fin)->format('H:i') }}"
                                   readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="mb-4">
                    <h5 class="font-weight-semibold text-muted mb-3">
                        Observación General
                    </h5>
                    <div class="p-3 bg-light rounded">
                        <textarea class="form-control" rows="3" readonly>{{ $limpieza->observaciones ?? '' }}</textarea>
                    </div>
                </div>

                <h5 class="font-weight-semibold text-muted mb-3">
                    Asignación de Tareas
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Tarea</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($estudiantes->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                                            <p class="mb-0">No hay estudiantes asignados a esta sección</p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach($estudiantesConTareas as $estudianteData)
                                    <tr class="border-bottom">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="logoest bg-primary text-white font-weight-bold mr-3">
                                                    {{ $estudianteData['estudiante']->nombres[0] }}{{ $estudianteData['estudiante']->apellidos[0] }}
                                                </div>
                                                <div>
                                                    <div class="font-weight-medium">{{ $estudianteData['estudiante']->nombres }} {{ $estudianteData['estudiante']->apellidos }}</div>
                                                    <div class="text-muted small">ID: {{ $estudianteData['estudiante']->id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" 
                                                       class="form-control form-control-lg" 
                                                       value="{{ $estudianteData['tarea'] }}"
                                                       readonly>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       disabled
                                                       {{ $estudianteData['realizada'] ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <textarea class="form-control form-control-lg" 
                                                          rows="2" 
                                                          readonly>{{ $estudianteData['observaciones'] }}</textarea>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 pt-3">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('limpiezas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Regresar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .logoest {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        font-size: 0.875rem;
    }
</style>
@stop