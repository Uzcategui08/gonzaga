@extends('adminlte::page')

@section('title', 'Editar Clase Extracurricular')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Editar Clase Extracurricular</h1>
    <a href="{{ route('extracurricular.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-body p-3">
            <form action="{{ route('extracurricular.clases.update', $clase->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="nombre" class="font-weight-bold">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $clase->nombre) }}" class="form-control form-control-lg @error('nombre') is-invalid @enderror" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="profesor_id" class="font-weight-bold">Profesor *</label>
                        <select id="profesor_id" name="profesor_id" class="form-control form-control-lg select2 @error('profesor_id') is-invalid @enderror" required>
                            <option value="">Seleccione un profesor</option>
                            @foreach(($profesores ?? collect()) as $profesor)
                                @php
                                    $selectedProfesor = old('profesor_id', $clase->profesor_id);
                                @endphp
                                <option value="{{ $profesor->id }}" {{ (string) $selectedProfesor === (string) $profesor->id ? 'selected' : '' }}>
                                    {{ $profesor->user?->name ?? ('Profesor #' . $profesor->id) }}
                                </option>
                            @endforeach
                        </select>
                        @error('profesor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label for="aula" class="font-weight-bold">Aula</label>
                        <input type="text" id="aula" name="aula" value="{{ old('aula', $clase->aula) }}" class="form-control form-control-lg @error('aula') is-invalid @enderror" placeholder="Opcional">
                        @error('aula')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="hora_inicio" class="font-weight-bold">Hora inicio *</label>
                        <input type="time" id="hora_inicio" name="hora_inicio" value="{{ old('hora_inicio', $clase->hora_inicio) }}" class="form-control form-control-lg @error('hora_inicio') is-invalid @enderror" required>
                        @error('hora_inicio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="hora_fin" class="font-weight-bold">Hora fin *</label>
                        <input type="time" id="hora_fin" name="hora_fin" value="{{ old('hora_fin', $clase->hora_fin) }}" class="form-control form-control-lg @error('hora_fin') is-invalid @enderror" required>
                        @error('hora_fin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @php
                    $diasSemana = [
                        1 => 'Lunes',
                        2 => 'Martes',
                        3 => 'Miércoles',
                        4 => 'Jueves',
                        5 => 'Viernes',
                        6 => 'Sábado',
                        7 => 'Domingo',
                    ];
                    $diaSeleccionado = old('dia_semana', $clase->dia_semana);
                @endphp
                <div class="form-group">
                    <label for="dia_semana" class="font-weight-bold">Día de la semana *</label>
                    <select id="dia_semana" name="dia_semana" class="form-control form-control-lg @error('dia_semana') is-invalid @enderror" required>
                        <option value="">Seleccione un día</option>
                        @foreach($diasSemana as $valor => $label)
                            <option value="{{ $valor }}" {{ (string) $diaSeleccionado === (string) $valor ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('dia_semana')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="descripcion" class="font-weight-bold">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $clase->descripcion) }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="estudiantes_id" class="font-weight-bold">Estudiantes (mixtos) *</label>
                    @php
                        $idsSeleccionados = old('estudiantes_id', $seleccionados ?? []);
                    @endphp
                    <select id="estudiantes_id" name="estudiantes_id[]" class="form-control form-control-lg select2 @error('estudiantes_id') is-invalid @enderror" multiple required>
                        @php
                            $porSeccion = ($estudiantes ?? collect())->groupBy(function($e) {
                                $grado = $e->seccion?->grado?->nombre;
                                $seccion = $e->seccion?->nombre;
                                return trim(($grado ? $grado . ' - ' : '') . ($seccion ?? 'Sin sección'));
                            });
                        @endphp
                        @foreach($porSeccion as $seccionLabel => $lista)
                            <optgroup label="{{ $seccionLabel }}">
                                @foreach($lista as $e)
                                    <option value="{{ $e->id }}" {{ in_array($e->id, $idsSeleccionados) ? 'selected' : '' }}>
                                        {{ $e->apellidos }} {{ $e->nombres }} ({{ $e->codigo_estudiante }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('estudiantes_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-3">
                    <div class="custom-control custom-switch">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1" {{ old('activo', $clase->activo) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="activo">Clase activa</label>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-start mt-4">
                    <button type="submit" class="btn btn-primary btn-lg mr-2 mb-2">
                        <i class="fas fa-save mr-1"></i> Guardar cambios
                    </button>
                    <a href="{{ route('extracurricular.index') }}" class="btn btn-secondary btn-lg mb-2">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Seleccione estudiantes'
        });

        $('#profesor_id').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Seleccione un profesor'
        });
    });
</script>
@endpush
