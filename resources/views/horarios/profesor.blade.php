@extends('adminlte::page')

@section('title', 'Horario Profesor')

@section('content_header')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="m-0 text-dark">
            @php
                $profesor = auth()->user()->profesor;
                $profesorName = $profesor && $profesor->user ? $profesor->user->name : auth()->user()->name;
            @endphp
            Horario Semanal - {{ $profesorName }}
        </h1>
        <small class="text-muted">
            Semana del {{ now()->startOfWeek()->format('d/m/Y') }} al {{ now()->endOfWeek()->format('d/m/Y') }}
        </small>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    @if(isset($horarios))
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                @if($horarios->isEmpty())
                    <div class="alert alert-info m-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x mr-3"></i>
                            <div>
                                <h5 class="mb-2">Sin Horarios Asignados</h5>
                                <p class="mb-1">Actualmente no tienes horarios asignados.</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered m-0">
                            <thead>
                                <tr>
                                    @foreach($dias as $dia)
                                        @php
                                            $fecha = now()->startOfWeek(); 
                                            switch($dia) {
                                                case 'Lunes': break;
                                                case 'Martes': $fecha->addDay(); break;
                                                case 'Miércoles': $fecha->addDays(2); break;
                                                case 'Jueves': $fecha->addDays(3); break;
                                                case 'Viernes': $fecha->addDays(4); break;
                                                case 'Sábado': $fecha->addDays(5); break;
                                                case 'Domingo': $fecha->addDays(6); break;
                                            }
                                            
                                            $fechaFormateada = $fecha->format('d/m');
                                            $nombreCorto = [
                                                'Lunes' => 'LUN',
                                                'Martes' => 'MAR',
                                                'Miércoles' => 'MIÉ',
                                                'Jueves' => 'JUE',
                                                'Viernes' => 'VIE',
                                                'Sábado' => 'SÁB',
                                                'Domingo' => 'DOM'
                                            ][$dia];
                                        @endphp
                                        <th style="width: 18%;" class="text-center py-2">
                                            <div class="font-weight-bold">{{ $nombreCorto }}</div>
                                            <div class="small">{{ $fechaFormateada }}</div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $horasInicio = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', 
                                                   '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
                                @endphp
                                
                                @foreach($horasInicio as $horaInicio)
                                    @php
                                        $horaFin = date('H:i', strtotime($horaInicio) + 60*60);
                                        $horaDisplay = substr($horaInicio, 0, 5).' - '.substr($horaFin, 0, 5);
                                        // Verificar si hay al menos una clase en esta franja horaria
                                        $clasesPorDia = [];
                                        $hayClaseEnFranja = false;
                                        foreach($dias as $dia) {
                                            $clasesEnEsteHorario = $horarios->filter(function($horario) use ($dia, $horaInicio, $horaFin) {
                                                return $horario->dia === $dia && 
                                                       $horario->hora_inicio >= $horaInicio && 
                                                       $horario->hora_inicio < $horaFin;
                                            });
                                            $clasesPorDia[$dia] = $clasesEnEsteHorario;
                                            if(!$clasesEnEsteHorario->isEmpty()) {
                                                $hayClaseEnFranja = true;
                                            }
                                        }
                                    @endphp
                                    @if($hayClaseEnFranja)
                                    <tr>
                                        @foreach($dias as $dia)
                                            @php
                                                $clasesEnEsteHorario = $clasesPorDia[$dia];
                                            @endphp
                                            <td class="p-2 @if($clasesEnEsteHorario->isEmpty()) bg-light @endif">
                                                @foreach($clasesEnEsteHorario as $horario)
                                                    <div class="card mb-2 border-left-3 border-primary">
                                                        <div class="card-body p-2">
                                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                                <h6 class="card-title mb-0 text-primary font-weight-bold">
                                                                    {{ $horario->asignacion->materia->nombre }}
                                                                </h6>
                                                                <span class="badge badge-light text-dark">
                                                                    {{ substr($horario->hora_inicio, 0, 5) }} - {{ substr($horario->hora_fin, 0, 5) }}
                                                                </span>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <div class="d-flex align-items-center">
                                                                        <i class="fas fa-users mr-2"></i>
                                                                        <div class="d-flex flex-column">
                                                                            <span class="text-muted small">
                                                                                {{ $horario->asignacion->seccion->grado->nombre }} {{ $horario->asignacion->seccion->nombre }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <span class="text-muted small d-block">
                                                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                                                        {{ $horario->aula ?? 'Aula por asignar' }}
                                                                    </span>
                                                                </div>
                                                                <!--
                                                                <a href="{{ route('asistencias.registrar', [$horario->asignacion->materia->id, $horario->id]) }}" 
                                                                   class="btn btn-sm btn-primary rounded-circle p-0"
                                                                   style="width:24px;height:24px;"
                                                                   title="Registrar asistencia">
                                                                    <i class="fas fa-plus" style="font-size:0.8rem;"></i>
                                                                </a>
                                                                -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </td>
                                        @endforeach
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                <div>
                    <h5 class="mb-2">Error al cargar el horario</h5>
                    <p class="mb-1">Por favor contacte al administrador del sistema.</p>
                </div>
            </div>
        </div>
    @endif
</div>
@stop

@section('css')
<style>
    .table {
        table-layout: fixed;
        font-size: 0.85rem;
        width: 100%;
        min-width: 800px;
    }
    
    .table-responsive {
        overflow-x: auto;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .table th {
        vertical-align: middle;
        background-color: #f8f9fa !important;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    
    .table td {
        vertical-align: top;
        height: auto;
        border: 1px solid #e9ecef;
        padding: 0.75rem;
    }
    
    .card {
        transition: all 0.2s ease;
        border-radius: 0.25rem;
        border-left: 3px solid #2c6aa0 !important;
        background-color: #f8fafc;
        margin-bottom: 0.75rem;
    }
    
    .card:hover {
        background-color: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .border-left-3 {
        border-left-width: 3px !important;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    .text-muted {
        font-size: 0.85rem;
    }
    
    .card-title {
        font-size: 1rem;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }
        .table {
            font-size: 0.85rem;
        }
        .card-title-horario {
            font-size: 0.8rem;
        }
    }
</style>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableContainer = document.querySelector('.table-responsive');
        if (tableContainer) {
            tableContainer.addEventListener('scroll', function() {
                if (this.scrollLeft > 0) {
                    this.style.boxShadow = '5px 0 10px rgba(0,0,0,0.1)';
                } else {
                    this.style.boxShadow = 'none';
                }
            });
        }
    });
</script>
@endsection
