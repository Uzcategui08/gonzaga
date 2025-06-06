@extends('adminlte::page')

@section('title', 'Detalles de la Asignación')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Detalles de la Asignación</h1>
    <div class="btn-group">
        <a href="{{ route('asignaciones.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-chalkboard-teacher text-primary mr-2"></i>
            </h3>
        </div>

        <div class="card-body p-0 p-md-3">
            <div class="row">
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card card-primary card-outline h-100">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-tie mr-2"></i>Profesor
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-12 col-lg-6 mb-2 mb-lg-0">
                                    <label class="font-weight-bold text-gray-700">Nombre</label>
                                    <p class="form-control-plaintext font-weight-bold text-primary text-truncate">
                                        {{ $asignacion->profesor->user->name }}
                                    </p>
                                </div>
                                <div class="col-12 col-sm-6 col-md-12 col-lg-6">
                                    <label class="font-weight-bold text-gray-700">Código</label>
                                    <p class="form-control-plaintext font-weight-bold text-secondary">
                                        {{ $asignacion->profesor->codigo_profesor }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card card-primary card-outline h-100">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-book mr-2"></i>Materia
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-12 col-lg-6 mb-2 mb-lg-0">
                                    <label class="font-weight-bold text-gray-700">Materia</label>
                                    <p class="form-control-plaintext font-weight-bold text-primary text-truncate">
                                        {{ $asignacion->materia->nombre }}
                                    </p>
                                </div>
                                <div class="col-12 col-sm-6 col-md-12 col-lg-6">
                                    <label class="font-weight-bold text-gray-700">Nivel</label>
                                    <p class="form-control-plaintext font-weight-bold text-secondary">
                                        {{ $asignacion->materia->nivel }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card card-primary card-outline h-100">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users mr-2"></i>Sección
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-12 col-lg-6 mb-2 mb-lg-0">
                                    <label class="font-weight-bold text-gray-700">Sección</label>
                                    <p class="form-control-plaintext font-weight-bold text-primary">
                                        {{ $asignacion->seccion->nombre }}
                                    </p>
                                </div>
                                <div class="col-12 col-sm-6 col-md-12 col-lg-6">
                                    <label class="font-weight-bold text-gray-700">Grado</label>
                                    <p class="form-control-plaintext font-weight-bold text-secondary">
                                        {{ $asignacion->seccion->grado->nombre }}
                                    </p>
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
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem !important;
            }
            .form-control-plaintext {
                font-size: 0.9rem;
            }
        }

        .card {
            min-height: 100%;
        }

        @media (max-width: 576px) {
            .mb-4 {
                margin-bottom: 1.5rem !important;
            }
        }
    </style>
@stop