@extends('adminlte::page')

@section('title', 'Detalles del Estudiante')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        Detalles del Estudiante
    </h1>
    <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-id-card mr-2"></i>Información Personal
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
                                <span class="info-box-icon bg-primary"><i class="fas fa-id-badge"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Código</span>
                                    <span class="info-box-number">{{ $estudiante->codigo_estudiante }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info"><i class="fas fa-venus-mars"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Género</span>
                                    <span class="info-box-number">{{ ucfirst($estudiante->genero) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <dl class="row">
                        <dt class="col-sm-4">Nombre completo:</dt>
                        <dd class="col-sm-8">
                            {{ $estudiante->apellidos }}, {{ $estudiante->nombres }}
                            <span class="badge py-1 px-2 ml-2 align-middle 
                                @if($estudiante->estado === 'activo') bg-success
                                @elseif($estudiante->estado === 'inactivo') bg-warning
                                @else bg-secondary
                                @endif">
                                <i class="fas 
                                    @if($estudiante->estado === 'activo') fa-check-circle
                                    @elseif($estudiante->estado === 'inactivo') fa-exclamation-circle
                                    @else fa-question-circle
                                    @endif"></i>
                                {{ ucfirst($estudiante->estado) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Fecha nacimiento:</dt>
                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($estudiante->fecha_nacimiento)->format('d/m/Y') }} 
                            <small class="text-muted">({{ \Carbon\Carbon::parse($estudiante->fecha_nacimiento)->age }} años)</small>
                        </dd>

                        <dt class="col-sm-4">Dirección:</dt>
                        <dd class="col-sm-8">{{ $estudiante->direccion ?? '<span class="text-muted">No especificada</span>' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-graduation-cap mr-2"></i>Información Académica
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success"><i class="fas fa-calendar-day"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Fecha ingreso</span>
                                    <span class="info-box-number">{{ \Carbon\Carbon::parse($estudiante->fecha_ingreso)->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-purple"><i class="fas fa-layer-group"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sección</span>
                                    <span class="info-box-number">{{ $estudiante->seccion->nombre }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Observaciones:</label>
                        <div class="border rounded p-3 bg-light">
                            {!! $estudiante->observaciones ? nl2br(e($estudiante->observaciones)) : '<span class="text-muted">No hay observaciones registradas</span>' !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-phone-alt mr-2"></i>Contacto de Emergencia
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="callout callout-info">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Nombre:</dt>
                            <dd class="col-sm-8">{{ $estudiante->contacto_emergencia_nombre }}</dd>

                            <dt class="col-sm-4">Parentesco:</dt>
                            <dd class="col-sm-8">{{ $estudiante->contacto_emergencia_parentesco }}</dd>

                            <dt class="col-sm-4">Teléfono:</dt>
                            <dd class="col-sm-8">
                                <a href="tel:{{ $estudiante->contacto_emergencia_telefono }}">
                                    {{ $estudiante->contacto_emergencia_telefono }}
                                </a>
                            </dd>
                        </dl>
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
    }
    .info-box-icon {
        font-size: 1.8rem;
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
    dt {
        font-weight: 600;
        color: #495057;
    }
    .callout {
        border-left-width: 5px;
    }
    .row.mb-4 {
        margin-bottom: 1.5rem !important;
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