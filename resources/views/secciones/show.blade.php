@extends('adminlte::page')

@section('title', 'Detalles de la Sección')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Detalles de la Sección</h1>
    <a href="{{ route('secciones.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-info-circle text-info mr-2"></i>Información de la Sección
            </h3>
        </div>

        <div class="card-body p-3">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-book text-primary mr-3" style="font-size: 1.5rem"></i>
                        <div>
                            <p class="mb-0 text-gray-600">Nombre de la Sección</p>
                            <h4 class="mb-0 text-dark font-weight-bold">{{ $seccion->nombre }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-graduation-cap text-success mr-3" style="font-size: 1.5rem"></i>
                        <div>
                            <p class="mb-0 text-gray-600">Grado Asociado</p>
                            <h4 class="mb-0 text-dark font-weight-bold">
                                <span class="badge badge-pill py-2 px-3 
                                    @if($seccion->grado->nivel == 'Primaria') badge-primary
                                    @elseif($seccion->grado->nivel == 'Secundaria') badge-info
                                    @else badge-secondary
                                    @endif">
                                    {{ $seccion->grado->nombre }} ({{ $seccion->grado->nivel }})
                                </span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('secciones.edit', $seccion) }}" class="btn btn-warning btn-lg">
                    <i class="fas fa-edit mr-1"></i> Editar
                </a>
                <a href="{{ route('secciones.index') }}" class="btn btn-secondary btn-lg ml-2">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
