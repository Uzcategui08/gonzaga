@extends('adminlte::page')

@section('title', 'Detalles del Profesor')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Detalles del Profesor</h1>
    <div class="btn-group">
        <a href="{{ route('profesores.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-chalkboard-teacher text-primary mr-2"></i>Información del Profesor
            </h3>
        </div>

        <div class="card-body p-3">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Información Básica</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Nombre</label>
                                <p class="form-control-plaintext">{{ $profesor->user->name }}</p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Email</label>
                                <p class="form-control-plaintext">{{ $profesor->user->email }}</p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Código de Profesor</label>
                                <p class="form-control-plaintext">{{ $profesor->codigo_profesor }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Información Académica</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Especialidad</label>
                                <p class="form-control-plaintext">{{ $profesor->especialidad }}</p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Tipo de Contrato</label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-pill py-2 px-3 
                                        @if($profesor->tipo_contrato == 'titular') badge-success
                                        @elseif($profesor->tipo_contrato == 'contratado') badge-info
                                        @else badge-warning
                                        @endif">
                                        {{ $profesor->tipo_contrato }}
                                    </span>
                                </p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Fecha de Contratación</label>
                                <p class="form-control-plaintext">{{ $profesor->fecha_contratacion->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
