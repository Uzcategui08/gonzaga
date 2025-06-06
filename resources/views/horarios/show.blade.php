@extends('adminlte::page')

@section('title', 'Detalles del Horario')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-1 text-dark">
        Detalles
    </h1>
    <a href="{{ route('horarios.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-primary card-outline">
                <div class="card-header bg-white">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-day mr-2"></i>Detalles del Horario
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-primary"><i class="far fa-calendar-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Día</span>
                                    <span class="info-box-number">{{ $horario->dia }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info"><i class="fas fa-door-open"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Aula</span>
                                    <span class="info-box-number">{{ $horario->aula }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success"><i class="far fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Hora Inicio</span>
                                    <span class="info-box-number">{{ $horario->hora_inicio }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Hora Fin</span>
                                    <span class="info-box-number">{{ $horario->hora_fin }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Duración: {{ date('H:i', strtotime($horario->hora_inicio)) }} - {{ date('H:i', strtotime($horario->hora_fin)) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-success card-outline">
                <div class="card-header bg-white">
                    <h3 class="card-title">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>Asignación Académica
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-tie fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mt-0 mb-1">{{ $horario->asignacion->profesor->user->name }}</h5>
                            <p class="mb-0 text-muted">
                                <i class="fas fa-id-card"></i> {{ $horario->asignacion->profesor->codigo_profesor }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-book fa-2x text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mt-0 mb-1">{{ $horario->asignacion->materia->nombre }}</h5>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users fa-2x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mt-0 mb-1">{{ $horario->asignacion->seccion->nombre }}</h5>
                            <p class="mb-0 text-muted">
                                <i class="fas fa-layer-group"></i> {{ $horario->asignacion->seccion->grado->nombre ?? '' }}
                            </p>
                        </div>
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
        margin-bottom: 15px;
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
    .card-title {
        font-weight: 600;
    }
    .flex-shrink-0 {
        width: 50px;
        text-align: center;
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