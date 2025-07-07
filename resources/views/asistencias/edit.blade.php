@extends('adminlte::page')

@section('title', 'Editar Asistencia')

@section('content_header')
    <h1>Editar Asistencia</h1>
@stop

@section('content')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkProfesorObservacion = document.getElementById('tiene_observacion_profesor');
    const observacionProfesor = document.getElementById('profesor_observacion');
    const observacionProfesorContainer = document.getElementById('observacion_profesor_container');

    checkProfesorObservacion.addEventListener('change', function() {
        if (this.checked) {
            observacionProfesorContainer.style.display = 'block';
            observacionProfesor.required = true;
        } else {
            observacionProfesorContainer.style.display = 'none';
            observacionProfesor.required = false;
            observacionProfesor.value = '';
        }
    });
});
</script>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('asistencias.update', $asistencia->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="fecha">Fecha de Clase</label>
                            <input type="date" 
                                   class="form-control @error('fecha') is-invalid @enderror" 
                                   id="fecha" 
                                   name="fecha" 
                                   value="{{ old('fecha', $asistencia->fecha ? $asistencia->fecha->format('Y-m-d') : '') }}" 
                                   required>
                            @error('fecha')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="hora_inicio">Hora de Inicio</label>
                            <input type="time" 
                                   class="form-control @error('hora_inicio') is-invalid @enderror" 
                                   id="hora_inicio" 
                                   name="hora_inicio" 
                                   value="{{ old('hora_inicio', $asistencia->hora_inicio) }}" 
                                   required>
                            @error('hora_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="contenido_clase">Contenido de la Clase *</label>
                            <textarea class="form-control @error('contenido_clase') is-invalid @enderror" 
                                      id="contenido_clase" 
                                      name="contenido_clase" 
                                      rows="3" 
                                      required
                                      placeholder="Describa el contenido tratado en la clase">{{ old('contenido_clase', $asistencia->contenido_clase) }}</textarea>
                            @error('contenido_clase')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="card mt-4">
                            <div class="card-header bg-light">
                                <h3 class="card-title mb-0">Registro de Estudiantes</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="30%">Estudiante</th>
                                                <th width="15%">Estado</th>
                                                <th width="55%">Observación Individual</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                if(isset($estudiantesConPase) && !empty($estudiantesConPase)) {
                                                    $pasesActivos = collect($estudiantesConPase);
                                                } else {
                                                    $pasesActivos = collect([]);
                                                }
                                            @endphp
                                            @foreach($asistencia->estudiantes as $estudiante)
                                            <tr>
                                                <td>
                                                    <strong>{{ $estudiante->nombres }} {{ $estudiante->apellidos }}</strong>
                                                    @if($pasesActivos->contains($estudiante->estudiante_id))
                                                        <div class="d-flex align-items-center mt-1">
                                                            <span class="badge badge-pill py-2 px-3 badge-warning mr-2">Pase Activo</span>
                                                        </div>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">ID: {{ $estudiante->estudiante_id }}</small>
                                                </td>
                                                <td>
                                                    <select class="form-control form-control-sm" 
                                                            name="estudiantes[{{ $estudiante->estudiante_id }}][estado]" 
                                                            required>
                                                        <option value="A" {{ old('estudiantes.' . $estudiante->estudiante_id . '.estado', $estudiante->estado) === 'A' ? 'selected' : '' }}>Asistente</option>
                                                        <option value="I" {{ old('estudiantes.' . $estudiante->estudiante_id . '.estado', $estudiante->estado) === 'I' ? 'selected' : '' }}>Inasistente</option>
                                                        <option value="P" {{ old('estudiantes.' . $estudiante->estudiante_id . '.estado', $estudiante->estado) === 'P' ? 'selected' : '' }}>Pase</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <textarea class="form-control form-control-sm" 
                                                              name="estudiantes[{{ $estudiante->estudiante_id }}][observacion_individual]" 
                                                              rows="2"
                                                              placeholder="Observaciones específicas">{{ old('estudiantes.' . $estudiante->estudiante_id . '.observacion_individual', $estudiante->observacion_individual) }}</textarea>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card card-outline card-primary">
                                    <div class="card-body">
                                        <div class="form-group pl-3">
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="falta_justificada" 
                                                       name="falta_justificada"
                                                       {{ old('falta_justificada', $asistencia->falta_justificada) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="falta_justificada">Falta Justificada</label>
                                            </div>

                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="tarea_pendiente" 
                                                       name="tarea_pendiente"
                                                       {{ old('tarea_pendiente', $asistencia->tarea_pendiente) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="tarea_pendiente">Tarea Pendiente</label>
                                            </div>

                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="conducta" 
                                                       name="conducta"
                                                       {{ old('conducta', $asistencia->conducta) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="conducta">Problemas de Conducta</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card card-outline card-primary">
                                    <div class="card-body">
                                        <div class="form-group pl-3">
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="pase_salida" 
                                                       name="pase_salida"
                                                       {{ old('pase_salida', $asistencia->pase_salida) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="pase_salida">Pase de Salida</label>
                                            </div>

                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="retraso" 
                                                       name="retraso"
                                                       {{ old('retraso', $asistencia->retraso) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="retraso">Retraso</label>
                                            </div>

                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="s_o" 
                                                       name="s_o"
                                                       {{ old('s_o', $asistencia->s_o) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="s_o">S/O</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="observacion_general">Observación General</label>
                            <textarea class="form-control @error('observacion_general') is-invalid @enderror" 
                                      id="observacion_general" 
                                      name="observacion_general" 
                                      rows="3"
                                      placeholder="Observaciones generales de la clase">{{ old('observacion_general', $asistencia->observacion_general) }}</textarea>
                            @error('observacion_general')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mt-4">
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="tiene_observacion_profesor" 
                                       name="tiene_observacion_profesor"
                                       {{ old('tiene_observacion_profesor', !empty($asistencia->profesor_observacion)) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="tiene_observacion_profesor">Desea guardar una nota personal</label>
                            </div>
                        </div>

                        <div class="form-group mt-4" id="observacion_profesor_container" 
                             style="{{ old('tiene_observacion_profesor', !empty($asistencia->profesor_observacion)) ? 'display: block;' : 'display: none;' }}">
                            <label for="profesor_observacion">Observación Personal del Profesor</label>
                            <textarea class="form-control @error('profesor_observacion') is-invalid @enderror" 
                                      id="profesor_observacion" 
                                      name="profesor_observacion" 
                                      rows="3"
                                      placeholder="Escriba su observación personal">{{ old('profesor_observacion', $asistencia->profesor_observacion) }}</textarea>
                            @error('profesor_observacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mt-4">
                            <div class="card border-warning">
                                <div class="card-body bg-light-warning">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="aprobado" 
                                               name="aprobado" 
                                               required
                                               {{ old('aprobado', $asistencia->aprobado) ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold text-warning-dark" for="aprobado">
                                            CONFIRMO QUE LA INFORMACIÓN REGISTRADA ES CORRECTA Y APRUEBO LA ASISTENCIA
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    @import url('https://cdn.jsdelivr.net/npm/sweetalert2@11');
</style>
@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const estudiantesConPase = @json($estudiantesConPase);

        const selectoresEstado = document.querySelectorAll('select[name^="estudiantes"][name$="[estado]"]');

        selectoresEstado.forEach(select => {
            const estudianteId = select.name.match(/\[(\d+)\]/)[1];

            select.addEventListener('change', function() {
                const valorSeleccionado = this.value;
                const tienePase = estudiantesConPase.includes(parseInt(estudianteId));

                if (valorSeleccionado === 'P' && !tienePase) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Este estudiante no tiene un pase activo',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        const opciones = this.options;
                        for (let i = 0; i < opciones.length; i++) {
                            if (opciones[i].value !== 'P') {
                                this.value = opciones[i].value;
                                break;
                            }
                        }
                    });
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const checkProfesorObservacion = document.getElementById('tiene_observacion_profesor');
        const observacionProfesor = document.getElementById('profesor_observacion');
        const observacionProfesorContainer = document.getElementById('observacion_profesor_container');

        checkProfesorObservacion.addEventListener('change', function() {
            if (this.checked) {
                observacionProfesorContainer.style.display = 'block';
                observacionProfesor.required = true;
            } else {
                observacionProfesorContainer.style.display = 'none';
                observacionProfesor.required = false;
                observacionProfesor.value = '';
            }
        });
    });
</script>
@endsection
@section('css')
<style>
    .custom-control-input:checked~.custom-control-label::before {
        border-color: #007bff;
        background-color: #007bff;
    }
    
    .custom-control-label {
        cursor: pointer;
        padding-left: 5px;
    }
    
    .custom-checkbox {
        padding-left: 1.75rem;
    }

    .bg-light-warning {
        background-color: #fff3cd;
    }
    
    .text-warning-dark {
        color: #856404;
    }

    .card-outline {
        border-top: 3px solid #007bff !important;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            border: 0;
        }
        .table thead {
            display: none;
        }
        .table tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
        }
        .table td {
            display: block;
            text-align: right;
            padding-left: 50%;
            position: relative;
            border-bottom: 1px solid #dee2e6;
        }
        .table td::before {
            content: attr(data-label);
            position: absolute;
            left: 1rem;
            width: calc(50% - 1rem);
            padding-right: 1rem;
            font-weight: bold;
            text-align: left;
        }
        .table td:last-child {
            border-bottom: 0;
        }
    }
    
    @media (max-width: 576px) {
        .d-flex.justify-content-between {
            flex-direction: column;
        }
        .d-flex.justify-content-between .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>
@stop
