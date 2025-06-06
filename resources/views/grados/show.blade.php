@extends('adminlte::page')

@section('title', 'Detalles del Grado')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">
        Detalles del Grado
    </h1>
    <a href="{{ route('grados.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                    </h3>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info text-white"><i class="fas fa-book"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Nombre del Grado</span>
                                    <span class="info-box-number">{{ $grado->nombre }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-primary text-white"><i class="fas fa-graduation-cap"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Nivel Educativo</span>
                                    <span class="info-box-number">
                                        <span class="badge 
                                            @if($grado->nivel == 'Primaria') bg-primary
                                            @elseif($grado->nivel == 'Secundaria') bg-info
                                            @else bg-secondary
                                            @endif">
                                            {{ $grado->nivel }}
                                        </span>
                                    </span>
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
    .card-title {
        font-weight: 600;
    }
</style>
@stop