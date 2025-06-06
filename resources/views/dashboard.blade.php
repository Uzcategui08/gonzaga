@extends('adminlte::page')

@section('title', 'Inicio')

@section('content_header')
<h2 >Bienvenido!</h2>
<hr>
@stop

@section('content')

<head>
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
</head>



<section class="content">
    <div class="container-fluid">
        @if(auth()->check())
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-0">
                            @if(auth()->user()->hasRole('profesor'))
                                ¡Hola Profesor!
                            @elseif(auth()->user()->hasRole('admin'))
                                ¡Hola Administrador!
                            @elseif(auth()->user()->hasRole('coordinador'))
                                ¡Hola Coordinador!
                            @else
                                ¡Bienvenido!
                            @endif
                        </h4>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-3 col-6">

                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>4</h3>
                        <p>Productos en almacen</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>

                </div>
            </div>

            <div class="col-lg-3 col-6">

                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><sup style="font-size: 20px">%</sup></h3>
                        <p>Evolucion de Facturación</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>

                </div>
            </div>

            <div class="col-lg-3 col-6">

                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>4</h3>
                        <p>Ventas del mes</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>

                </div>
            </div>

            <div class="col-lg-3 col-6">

                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>4</h3>
                        <p>Facturación Mensual</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>

                </div>
            </div>

        </div>
        <div class="row">
    <!-- Card de Ventas por Lugar - Versión Compacta -->
    <section class="col-lg-7 connectedSortable mb-4">
        <div class="card shadow-lg border-0 h-100">
            <div class="card-header bg-gradient-primary text-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-line mr-2"></i>
                        Ventas por Lugar
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool text-white" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0" style="height: 300px;">
                <canvas id="ventasPorLugar" style="width: 100%; height: 100%; padding: 15px;"></canvas>
            </div>
        </div>
    </section>

    <!-- Card de Ventas por Técnico - Versión Compacta -->
    <section class="col-lg-5 connectedSortable mb-4">
        <div class="card shadow-lg border-0 h-100">
            <div class="card-header bg-gradient-info text-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-users mr-2"></i>
                        Ventas por Técnico
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool text-white" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0" style="height: 300px;">
                <canvas id="ventasPorTecnico" style="width: 100%; height: 100%; padding: 15px;"></canvas>
            </div>
        </div>
    </section>
</div>
<style>
    .card-footer {
        display: flex;
        justify-content: center;
        /* Centra horizontalmente */
        align-items: center;
        /* Centra verticalmente */
        height: 485px;
        /* Ajusta esta altura según tus necesidades */
    }

    canvas {
        max-width: 100%;
        /* Asegura que el canvas no exceda el ancho del contenedor */
        height: 485;
        /* Mantiene la proporción del canvas */
    }

    .chart-container {
    background-color: #f8f9fa;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    padding: 20px;
    transition: all 0.3s ease;
}

.chart-container:hover {
    box-shadow: 0 6px 16px rgba(0,0,0,0.15);
}
/* Para un ajuste más fino en móviles */
@media (max-width: 768px) {
    .card-body {
        height: 250px !important;
    }
    
    .card-header h3 {
        font-size: 1rem;
    }
}
</style>




@stop

@section('css')
{{-- --}}
<link rel="stylesheet" href="{{ asset('/build/assets/admin/admin.css') }}">

@stop

