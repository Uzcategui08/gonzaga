@extends('adminlte::page')

@section('title', 'Editar Limpieza')

@section('content_header')
    <h1>Editar Limpieza</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('limpiezas.update', $limpieza->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Información de la Limpieza</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha">Fecha</label>
                                    <input type="date" 
                                           class="form-control @error('fecha') is-invalid @enderror" 
                                           id="fecha" 
                                           name="fecha"
                                           value="{{ old('fecha', $limpieza->fecha->format('Y-m-d')) }}"
                                           required>
                                    @error('fecha')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="hora_inicio">Hora Inicio</label>
                                    <input type="time" 
                                           class="form-control @error('hora_inicio') is-invalid @enderror" 
                                           id="hora_inicio" 
                                           name="hora_inicio"
                                           value="{{ old('hora_inicio', $limpieza->hora_inicio->format('H:i')) }}"
                                           required>
                                    @error('hora_inicio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="hora_fin">Hora Fin</label>
                                    <input type="time" 
                                           class="form-control @error('hora_fin') is-invalid @enderror" 
                                           id="hora_fin" 
                                           name="hora_fin"
                                           value="{{ old('hora_fin', $limpieza->hora_fin->format('H:i')) }}"
                                           required>
                                    @error('hora_fin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Estado de la Limpieza</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="realizada" value="1" 
                                            {{ $limpieza->realizada ? 'checked' : '' }}>
                                        ¿La limpieza se realizó?
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="observaciones">Observaciones</label>
                                    <textarea class="form-control" 
                                              id="observaciones" 
                                              name="observaciones"
                                              rows="3">{{ old('observaciones', $limpieza->observaciones) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Estudiantes y Tareas</h3>
                    </div>
                    <div class="card-body">
                        @if($estudiantes->isEmpty())
                            <div class="alert alert-warning">
                                No se han encontrado estudiantes asignados a esta limpieza.
                            </div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Estudiante</th>
                                        <th>Tarea</th>
                                        <th>Realizada</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $tareasPorEstudiante = $tareasPorEstudiante ?? collect();
                                    @endphp

                                    @foreach($estudiantes as $estudiante)
                                        @php
                                            $tareaData = $tareasPorEstudiante->get($estudiante->id, []);
                                        @endphp
                                        
                                        <tr class="border-bottom">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="logoest bg-primary text-white font-weight-bold mr-3">
                                                        {{ $estudiante->nombres[0] }}{{ $estudiante->apellidos[0] }}
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-medium">{{ $estudiante->nombres }} {{ $estudiante->apellidos }}</div>
                                                        <div class="text-muted small">ID: {{ $estudiante->id }}</div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="estudiantes_tareas[{{ $estudiante->id }}][id]" value="{{ $estudiante->id }}">
                                            </td>
                                            <td>
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="estudiantes_tareas[{{ $estudiante->id }}][tarea]"
                                                       value="{{ $tareaData['tarea'] ?? '' }}"
                                                       required
                                                       placeholder="Ej: Limpiar pizarrón...">
                                            </td>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="estudiantes_tareas[{{ $estudiante->id }}][realizada]"
                                                           value="1"
                                                           {{ isset($tareaData['realizada']) && $tareaData['realizada'] ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td>
                                                <textarea class="form-control" 
                                                          name="estudiantes_tareas[{{ $estudiante->id }}][observaciones]"
                                                          rows="2">{{ $tareaData['observaciones'] ?? '' }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Actualizar Limpieza
                    </button>
                    <a href="{{ route('limpiezas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    const horaInicio = document.getElementById('hora_inicio');
    const horaFin = document.getElementById('hora_fin');

    horaInicio.addEventListener('change', validarHoras);
    horaFin.addEventListener('change', validarHoras);

    function validarHoras() {
        if (horaInicio.value && horaFin.value) {
            const inicio = new Date(`1970-01-01T${horaInicio.value}`);
            const fin = new Date(`1970-01-01T${horaFin.value}`);
            
            if (fin <= inicio) {
                horaFin.classList.add('is-invalid');
                horaFin.setCustomValidity('La hora fin debe ser mayor que la hora inicio');
            } else {
                horaFin.classList.remove('is-invalid');
                horaFin.setCustomValidity('');
            }
        }
    }
});
</script>
@endpush
@stop
