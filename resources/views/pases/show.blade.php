@extends('adminlte::page')

@section('title', 'Detalles del Pase')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Detalles del Pase</h1>
    <div class="d-flex align-items-center">
        <a href="{{ route('pases.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-graduate mr-2"></i>Información del Estudiante
                    </h3>
                </div>
                <div class="card-body text-center">
                    <div class="student-avatar mb-3">
                        <i class="fas fa-user-circle fa-5x text-secondary"></i>
                    </div>
                    <h4 class="mb-1">{{ $pase->estudiante->nombres }} {{ $pase->estudiante->apellidos }}</h4>
                    <p class="text-muted mb-2">
                        <i class="fas fa-id-card-alt mr-1"></i> 
                        {{ $pase->estudiante->codigo_estudiante }}
                    </p>
                    <p class="text-muted mb-0">
                        <i class="fas fa-layer-group mr-1"></i> 
                        {{ $pase->estudiante->seccion->nombre ?? 'Sin sección' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>Detalles del Pase
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-lg {{ $pase->aprobado ? 'bg-success' : 'bg-warning' }}">
                            {{ $pase->aprobado ? 'APROBADO' : 'PENDIENTE' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info"><i class="far fa-calendar-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Fecha y Hora</span>
                                    <span class="info-box-number">
                                        {{ $pase->fecha->format('d/m/Y') }} - 
                                        {{ $pase->hora_llegada->format('H:i') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-warning"><i class="fas fa-tag"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Motivo</span>
                                    <span class="info-box-number">
                                        <span class="badge bg-info">
                                            {{ $pase->motivo }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info mt-3">
                        <h5>Observaciones</h5>
                        <p>{{ $pase->observaciones ?: 'Sin observaciones' }}</p>
                    </div>

                    <div class="callout callout-info mt-3">
                        <h5>Información Adicional</h5>
                        <p>Creado por: {{ $pase->usuario->name }}</p>
                        <p class="text-muted">{{ $pase->created_at->diffForHumans(['locale' => 'es']) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
