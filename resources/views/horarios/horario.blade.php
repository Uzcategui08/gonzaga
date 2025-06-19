@extends('adminlte::page')

@section('title', 'Horario Profesor')

@section('content_header')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="m-0 text-dark">
            Horario del Profesor
        </h1>
        <small class="text-muted">
            Semana del {{ now()->startOfWeek()->format('d/m/Y') }} al {{ now()->endOfWeek()->format('d/m/Y') }}
        </small>
    </div>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form id="professorSelectorForm" method="GET" action="{{ route('horarios.profesor.admin') }}">
            <div class="form-group col-md-12">
                <label for="professor_id" class="font-weight-bold text-gray-700">Seleccionar Profesor:</label>
                <select name="professor_id" id="professor_id" class="form-control form-control-lg select2 @error('professor_id') is-invalid @enderror" required>
                    <option value="">-- Seleccione un profesor --</option>
                    @foreach($professors as $professor)
                        <option value="{{ $professor->id }}" 
                            @if(isset($selectedProfessor) && $selectedProfessor->id == $professor->id) selected @endif>
                            {{ $professor->user->name }} 
                            @if($professor->user->email)
                                ({{ $professor->user->email }})
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('professor_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </form>
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

    @if(isset($selectedProfessor))
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="fas fa-chalkboard-teacher fa-3x text-primary"></i>
                    </div>
                    <div>
                        <h4 class="mb-1">{{ $selectedProfessor->user->name }}</h4>
                        <p class="mb-1">
                            <span class="text-muted">Email:</span> 
                            {{ $selectedProfessor->user->email ?? 'No especificado' }}
                        </p>
                    </div>
                </div>
            </div>
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
                                <p class="mb-1">El profesor no tiene horarios asignados.</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered m-0">
                            <thead>
                                <tr>
                                    <th style="width: 10%;" class="text-center bg-light py-2">Hora</th>
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
                                    @endphp
                                    
                                    <tr>
                                        <td class="text-center bg-light font-weight-bold align-middle">
                                            <div class="py-2">{{ $horaDisplay }}</div>
                                        </td>
                                        
                                        @foreach($dias as $dia)
                                            @php
                                                $clasesEnEsteHorario = $horarios->filter(function($horario) use ($dia, $horaInicio, $horaFin) {
                                                    return $horario->dia === $dia && 
                                                           $horario->hora_inicio >= $horaInicio && 
                                                           $horario->hora_inicio < $horaFin;
                                                });
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
                                                                    {{ substr($horario->hora_inicio, 0, 5) }}
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
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
        border-radius: 0.25rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px) !important;
    }
    .card {
        transition: all 0.2s ease;
        border-radius: 0.25rem;
        border-left: 3px solid #2c6aa0 !important;
        background-color: #f8fafc;
    }
    .card:hover {
        background-color: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .border-left-3 {
        border-left-width: 3px !important;
    }
    .badge {
        font-size: 0.65rem;
        font-weight: 500;
        padding: 0.25em 0.4em;
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#professor_id').change(function() {
        if($(this).val()) {
            $('#professorSelectorForm').submit();
        }
    });
});

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
