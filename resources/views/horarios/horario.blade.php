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
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label for="search_type" class="font-weight-bold text-gray-700">Tipo de Búsqueda</label>
                        <select name="search_type" id="search_type" class="form-control form-control-lg select2">
                            <option value="">Seleccione una opción</option>
                            <option value="profesor">Buscar por Profesor</option>
                            <option value="seccion">Buscar por Sección</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="professor_id" class="font-weight-bold text-gray-700">Profesor</label>
                        <select name="professor_id" id="professor_id" class="form-control form-control-lg select2 @error('professor_id') is-invalid @enderror" required disabled>
                            <option value="">Seleccione una opción</option>
                            @foreach($professors as $professor)
                                <option value="{{ $professor->user->id }}" 
                                    @if(isset($selectedProfessor) && $selectedProfessor->user->id == $professor->user->id) selected @endif>
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
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="section_id" class="font-weight-bold text-gray-700">Sección</label>
                        <select name="section_id" id="section_id" class="form-control form-control-lg select2 @error('section_id') is-invalid @enderror" disabled>
                            <option value="">Seleccione una opción</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ old('section_id', request('section_id')) == $section->id ? 'selected' : '' }}>
                                    {{ $section->nombre }} - {{ $section->grado->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('section_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-lg" disabled>Filtrar</button>
                </div>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Horario del Profesor</h3>
                    <div class="text-muted">
                        <span class="font-weight-bold">{{ $selectedProfessor->user->name }}</span>
                        @if($selectedProfessor->user->email)
                            <small class="d-block">{{ $selectedProfessor->user->email }}</small>
                        @endif
                    </div>
                </div>
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle mr-2"></i>
                    Mostrando horarios para el profesor seleccionado
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
                                <h5 class="mb-2">Seleccione una opción</h5>
                                <p class="mb-1">Seleccione un profesor o sección para ver los horarios</p>
                            </div>
                        </div>
                    </div>
                @else
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
                                        @foreach($dias as $dia)
                                            @php
                                                $clasesEnEsteHorario = $horarios->filter(function($horario) use ($dia, $horaInicio, $horaFin) {
                                                    return $horario->dia === $dia && 
                                                           $horario->hora_inicio >= $horaInicio && 
                                                           $horario->hora_inicio < $horaFin;
                                                });
                                            @endphp
                                            
                                            <td class="p-2 @if($clasesEnEsteHorario->isEmpty()) bg-light @endif">
                                                @if(!$clasesEnEsteHorario->isEmpty())
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
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
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
    // Inicializar Select2
    $('.select2').select2();

    // Habilitar/deshabilitar selects según el tipo de búsqueda seleccionado
    function toggleSearchType() {
        const searchType = $('#search_type').val();
        
        if (searchType === 'profesor') {
            // Habilitar select de profesor y deshabilitar sección
            $('#professor_id').prop('disabled', false).trigger('change');
            $('#section_id').prop('disabled', true).trigger('change');
            $('#professor_id').val('');
            $('#section_id').val('');
        } else if (searchType === 'seccion') {
            // Habilitar select de sección y deshabilitar profesor
            $('#section_id').prop('disabled', false).trigger('change');
            $('#professor_id').prop('disabled', true).trigger('change');
            $('#professor_id').val('');
            $('#section_id').val('');
        } else {
            // Deshabilitar ambos selects
            $('#professor_id').prop('disabled', true).trigger('change');
            $('#section_id').prop('disabled', true).trigger('change');
            $('#professor_id').val('');
            $('#section_id').val('');
        }
        
        // Habilitar/deshabilitar el botón de filtrar
        updateFilterButton();
    }

    // Función para actualizar el estado del botón de filtrar
    function updateFilterButton() {
        const professorId = $('#professor_id').val();
        const sectionId = $('#section_id').val();
        const searchType = $('#search_type').val();
        
        // Habilitar el botón si hay un tipo de búsqueda seleccionado
        // y al menos uno de los selects tiene un valor
        const hasSelection = searchType && (professorId || sectionId);
        $('button[type="submit"]').prop('disabled', !hasSelection);
    }

    // Manejar cambios en el tipo de búsqueda
    $('#search_type').on('change', function(e) {
        toggleSearchType();
    });

    // Manejar cambios en los filtros
    $('#professor_id').on('change', function(e) {
        if (e.originalEvent) {
            toggleSearchType();
        }
        updateFilterButton();
    });

    $('#section_id').on('change', function(e) {
        if (e.originalEvent) {
            toggleSearchType();
        }
        updateFilterButton();
    });

    // Manejar el submit del formulario
    $('#professorSelectorForm').on('submit', function(e) {
        e.preventDefault();
        
        // Verificar si hay un filtro seleccionado
        const professorId = $('#professor_id').val();
        const sectionId = $('#section_id').val();
        const searchType = $('#search_type').val();
        
        if (!searchType) {
            alert('Por favor, seleccione el tipo de búsqueda.');
            return;
        }
        
        if (!professorId && !sectionId) {
            alert('Por favor, seleccione un profesor o una sección para filtrar.');
            return;
        }
        
        // Limpiar el otro filtro si hay uno seleccionado
        if (professorId) {
            $('#section_id').val('');
        } else if (sectionId) {
            $('#professor_id').val('');
        }
        
        // Enviar el formulario
        this.submit();
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