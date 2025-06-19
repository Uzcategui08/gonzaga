@extends('adminlte::page')

@section('title', 'Crear Limpieza')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Crear Limpieza</h1>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body p-4">
            <form action="{{ route('limpiezas.store') }}" method="POST" id="limpieza-form">
                @csrf
                
                <input type="hidden" name="profesor_id" value="{{ $horarioSeleccionado->asignacion->profesor_id }}">

                <div class="mb-4">
                    <h5 class="font-weight-semibold text-muted mb-3">
                        Información de la Clase
                    </h5>
                    <div class="p-3 bg-light rounded">
                        @if($horarioSeleccionado)
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-white text-dark border mr-2">
                                    <i class="far fa-clock text-primary mr-1"></i>{{ $horarioSeleccionado->hora_inicio }} - {{ $horarioSeleccionado->hora_fin }}
                                </span>
                                <span class="badge bg-white text-dark border mr-2">
                                    <i class="fas fa-book text-success mr-1"></i>{{ $horarioSeleccionado->asignacion->materia->nombre }}
                                </span>
                                <span class="badge bg-white text-dark border mr-2">
                                    <i class="fas fa-users text-warning mr-1"></i>{{ $horarioSeleccionado->asignacion->seccion->nombre }}
                                </span>
                                <span class="badge bg-white text-dark border mr-2">
                                    <i class="fas fa-door-open text-secondary mr-1"></i>{{ $horarioSeleccionado->aula }}
                                </span>
                                <span class="badge bg-white text-dark border">
                                    <i class="fas fa-chalkboard-teacher text-info mr-1"></i>{{ $horarioSeleccionado->asignacion->usuario->name ?? 'Sin profesor' }}
                                </span> 
                            </div>
                        @else
                            <p class="mb-0 text-muted">No se ha seleccionado una clase</p>
                        @endif
                    </div>
                    <input type="hidden" name="horario_id" value="{{ $horarioSeleccionado ? $horarioSeleccionado->id : '' }}">
                </div>

                <div class="mb-4">
                    <h5 class="font-weight-semibold text-muted mb-3">
                        Detalles de la Limpieza
                    </h5>
                    <div class="row">
                        <div class="form-group col-md-4 mb-3">
                            <label for="fecha" class="font-weight-bold text-gray-700">Fecha</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-calendar text-muted"></i>
                                    </span>
                                </div>
                                <input type="date" 
                                       class="form-control form-control-lg @error('fecha') is-invalid @enderror" 
                                       id="fecha" 
                                       name="fecha"
                                       value="{{ old('fecha', now('America/Caracas')->format('Y-m-d')) }}"
                                       required>
                                @error('fecha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group col-md-4 mb-3">
                            <label for="hora_inicio" class="font-weight-bold text-gray-700">Hora Inicio</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-clock text-muted"></i>
                                    </span>
                                </div>
                                <input type="time" 
                                       class="form-control form-control-lg @error('hora_inicio') is-invalid @enderror" 
                                       id="hora_inicio" 
                                       name="hora_inicio"
                                       value="{{ old('hora_inicio', $horarioSeleccionado ? $horarioSeleccionado->hora_inicio : '') }}"
                                       required>
                                @error('hora_inicio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group col-md-4 mb-3">
                            <label for="hora_fin" class="font-weight-bold text-gray-700">Hora Fin</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-clock text-muted"></i>
                                    </span>
                                </div>
                                <input type="time" 
                                       class="form-control form-control-lg @error('hora_fin') is-invalid @enderror" 
                                       id="hora_fin" 
                                       name="hora_fin"
                                       value="{{ old('hora_fin', $horarioSeleccionado ? $horarioSeleccionado->hora_fin : '') }}"
                                       required>
                                @error('hora_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h5 class="font-weight-semibold text-muted mb-3">
                       Asignación de Tareas
                    </h5>
                    <div id="estudiantes-container">
                        <div class="table-responsive">
                            <table id="students-table" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Estudiante</th>
                                        <th>Tarea</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-end">
                                <button type="button" id="agregar-fila" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus mr-2"></i>Agregar Estudiante
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="d-flex justify-content-start">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </button>
                        <a href="{{ route('limpiezas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .logoest {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        font-size: 0.875rem;
    }

    .btn-group-actions {
        gap: 1rem;
    }

    @media (max-width: 768px) {
        .btn-group-actions {
            gap: 0.5rem;
            flex-direction: column;
            align-items: stretch;
        }

        .btn-group-actions .btn {
            width: 100%;
        }
    }

    #agregar-fila {
        padding: 8px 16px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let contadorFilas = 0;

        function agregarFila(estudianteId = '', estudianteNombre = '', tarea = '') {
            contadorFilas++;
            const filaId = `fila-${contadorFilas}`;
            
            const nuevaFila = document.createElement('tr');
            nuevaFila.id = filaId;
            nuevaFila.className = 'border-bottom';

            const selectEstudiantes = document.createElement('select');
            selectEstudiantes.className = 'form-control-lg select2 select-estudiante @error('estudiantes.${contadorFilas}.id') is-invalid @enderror';
            selectEstudiantes.name = `estudiantes[${contadorFilas}][id]`;
            selectEstudiantes.required = true;

            const optionDefault = document.createElement('option');
            optionDefault.value = '';
            optionDefault.textContent = 'Seleccione un estudiante...';
            selectEstudiantes.appendChild(optionDefault);

            const estudiantesDisponibles = [
                @foreach($estudiantesDisponibles as $estudiante)
                    {
                        id: '{{ $estudiante->id }}',
                        nombres: '{{ $estudiante->nombres }}',
                        apellidos: '{{ $estudiante->apellidos }}'
                    }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ];

            estudiantesDisponibles.forEach(estudiante => {
                const opt = document.createElement('option');
                opt.value = estudiante.id;
                opt.textContent = `${estudiante.nombres} ${estudiante.apellidos}`;
                if (estudiante.id === estudianteId) {
                    opt.selected = true;
                }
                selectEstudiantes.appendChild(opt);
            });

            const tdSelect = document.createElement('td');
            tdSelect.appendChild(selectEstudiantes);

            const inputTarea = document.createElement('input');
            inputTarea.type = 'text';
            inputTarea.className = 'form-control';
            inputTarea.name = `estudiantes[${contadorFilas}][tarea]`;
            inputTarea.required = true;
            inputTarea.placeholder = 'Ej: Limpiar pizarrón...';
            inputTarea.value = tarea;

            const tdTarea = document.createElement('td');
            tdTarea.appendChild(inputTarea);

            const btnEliminar = document.createElement('button');
            btnEliminar.type = 'button';
            btnEliminar.className = 'btn btn-sm btn-light border btn-eliminar';
            btnEliminar.innerHTML = '<i class="fas fa-times text-danger"></i>';
            btnEliminar.onclick = function() {
                document.getElementById(filaId).remove();
            };

            const tdAcciones = document.createElement('td');
            tdAcciones.className = 'text-center';
            tdAcciones.appendChild(btnEliminar);

            nuevaFila.appendChild(tdSelect);
            nuevaFila.appendChild(tdTarea);
            nuevaFila.appendChild(tdAcciones);

            document.querySelector('#students-table tbody').appendChild(nuevaFila);

            $(selectEstudiantes).select2({
                width: '100%',
                placeholder: 'Seleccione un estudiante...',
                theme: 'bootstrap4'
            });

            $(selectEstudiantes).on('select2:select', function(e) {
                const selectedId = e.params.data.id;
                if (selectedId) {
                    const otherSelects = $('.select-estudiante');
                    let duplicateFound = false;

                    otherSelects.each(function() {
                        if (this !== selectEstudiantes && this.value === selectedId) {
                            duplicateFound = true;
                            $(selectEstudiantes).val('').trigger('change');
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Este estudiante ya está asignado a otra tarea',
                            });
                            
                            return false;
                        }
                    });
                }
            });
        }

        @if(old('estudiantes'))
            @foreach(old('estudiantes') as $index => $estudiante)
                agregarFila(
                    '{{ $estudiante["id"] }}', 
                    '', 
                    '{{ $estudiante["tarea"] ?? "" }}'
                );
            @endforeach
        @else
            agregarFila();
        @endif

        document.getElementById('agregar-fila').addEventListener('click', function() {
            agregarFila();
        });

        const horaInicio = document.getElementById('hora_inicio');
        const horaFin = document.getElementById('hora_fin');
        const form = document.getElementById('limpieza-form');

        function validarHoras() {
            if (horaInicio.value && horaFin.value) {
                const inicio = new Date(`1970-01-01T${horaInicio.value}`);
                const fin = new Date(`1970-01-01T${horaFin.value}`);
                
                if (fin <= inicio) {
                    horaFin.classList.add('is-invalid');
                    const errorElement = horaFin.nextElementSibling || document.createElement('div');
                    errorElement.className = 'invalid-feedback';
                    errorElement.textContent = 'La hora fin debe ser mayor que la hora inicio';
                    horaFin.parentNode.appendChild(errorElement);
                    return false;
                } else {
                    horaFin.classList.remove('is-invalid');
                    return true;
                }
            }
            return true;
        }

        horaInicio.addEventListener('change', validarHoras);
        horaFin.addEventListener('change', validarHoras);

        form.addEventListener('submit', function(e) {
            const selects = document.querySelectorAll('.select-estudiante');
            let alMenosUnEstudiante = false;
            
            selects.forEach(select => {
                if (select.value) {
                    alMenosUnEstudiante = true;
                }
            });
            
            if (!alMenosUnEstudiante) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe asignar al menos un estudiante',
                });
                return;
            }
            
            if (!validarHoras()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error en las horas',
                    text: 'La hora fin debe ser mayor que la hora inicio',
                });
            }
        });
        
    });
</script>
@stop