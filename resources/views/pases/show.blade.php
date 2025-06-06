@extends('adminlte::page')

@section('title', 'Detalles del Pase')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Detalles del Pase</h1>
        <a href="{{ route('pases.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        Detalles del Pase
                    </h3>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h3 class="card-title">Información Básica</h3>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">Estudiante:</dt>
                                        <dd class="col-sm-8">
                                            <strong>{{ $pase->estudiante->nombres }} {{ $pase->estudiante->apellidos }}</strong>
                                        </dd>

                                        <dt class="col-sm-4">Fecha:</dt>
                                        <dd class="col-sm-8">{{ $pase->fecha->format('d/m/Y') }}</dd>

                                        <dt class="col-sm-4">Hora de Llegada:</dt>
                                        <dd class="col-sm-8">{{ $pase->hora_llegada->format('H:i') }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h3 class="card-title">Detalles del Pase</h3>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">Motivo:</dt>
                                        <dd class="col-sm-8">{{ $pase->motivo }}</dd>

                                        <dt class="col-sm-4">Observaciones:</dt>
                                        <dd class="col-sm-8">{{ $pase->observaciones ?: 'Sin observaciones' }}</dd>

                                        <dt class="col-sm-4">Estado:</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge {{ $pase->aprobado ? 'badge-success' : 'badge-warning' }}">
                                                {{ $pase->aprobado ? 'Aprobado' : 'Pendiente' }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-4">Creado por:</dt>
                                        <dd class="col-sm-8">
                                            {{ $pase->usuario->name }}
                                            <small class="text-muted">
                                                ({{ $pase->created_at->diffForHumans() }})
                                            </small>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
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
        .card-header.bg-light {
            background-color: #f8f9fa !important;
        }
        dt {
            font-weight: 500;
        }
    </style>
@stop
