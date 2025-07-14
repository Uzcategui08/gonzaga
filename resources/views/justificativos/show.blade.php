@extends('adminlte::page')

@section('title', 'Detalles del Justificativo')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Detalles del Justificativo</h1>
    <div class="d-flex align-items-center">
        @if(auth()->user()->hasRole('profesor'))
            <a href="{{ route('justificativos.profesor') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver a mis justificativos
            </a>
        @else
            <a href="{{ route('justificativos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver al listado
            </a>
        @endif
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
                    <h4 class="mb-1">{{ $justificativo->estudiante->nombres }} {{ $justificativo->estudiante->apellidos }}</h4>
                    <p class="text-muted mb-2">
                        <i class="fas fa-id-card-alt mr-1"></i> 
                        {{ $justificativo->estudiante->codigo_estudiante }}
                    </p>
                    <p class="text-muted mb-0">
                        <i class="fas fa-layer-group mr-1"></i> 
                        {{ $justificativo->estudiante->seccion->grado->nombre ?? 'Sin grado' }} - 
                        {{ $justificativo->estudiante->seccion->nombre ?? 'Sin sección' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>Detalles del Justificativo
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-lg 
                            @if($justificativo->aprobado) bg-success
                            @else bg-warning
                            @endif">
                            {{ $justificativo->aprobado ? 'APROBADO' : 'PENDIENTE' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info"><i class="far fa-calendar-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Período</span>
                                    <span class="info-box-number">
                                        {{ $justificativo->fecha_inicio->format('d/m/Y') }} - 
                                        {{ $justificativo->fecha_fin->format('d/m/Y') }}
                                    </span>
                                    <span class="progress-description">
                                        {{ $justificativo->fecha_inicio->diffInDays($justificativo->fecha_fin) }} días
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-warning"><i class="fas fa-tag"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tipo</span>
                                    <span class="info-box-number">
                                        <span class="badge 
                                            @if($justificativo->tipo == 'salud') bg-warning
                                            @elseif($justificativo->tipo == 'familiar') bg-info
                                            @else bg-secondary
                                            @endif">
                                            {{ ucfirst($justificativo->tipo) }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info mt-3">
                        <h5><i class="fas fa-comment-dots mr-2"></i>Motivo</h5>
                        <p>{{ $justificativo->motivo }}</p>
                    </div>

                    <div class="callout callout-secondary mt-3">
                        <h5><i class="fas fa-clipboard-check mr-2"></i>Observaciones</h5>
                        <p>{{ $justificativo->observaciones ?? 'No hay observaciones registradas' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>
@stop

@section('css')
<style>
    .info-box {
        cursor: default;
        min-height: 80px;
        border-radius: .25rem;
        box-shadow: 0 0 1px rgba(0,0,0,0.1);
    }
    .info-box-icon {
        font-size: 1.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
    }
    .info-box-content {
        padding: 10px;
    }
    .info-box-text {
        font-size: 0.9rem;
        text-transform: uppercase;
        font-weight: 600;
        color: #6c757d;
    }
    .info-box-number {
        font-size: 1.2rem;
        font-weight: 700;
    }
    .student-avatar {
        color: #6c757d;
    }
    .document-card {
        transition: all 0.3s ease;
    }
    .document-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .badge-lg {
        font-size: 0.9rem;
        padding: 8px 12px;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@stop