@extends('adminlte::page')

@section('title', 'Nueva Clase Extracurricular')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Nueva Clase Extracurricular</h1>
    <a href="{{ route('extracurricular.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-body p-3">
            <form action="{{ route('extracurricular.clases.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="nombre" class="font-weight-bold">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" class="form-control form-control-lg @error('nombre') is-invalid @enderror" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="profesor_id" class="font-weight-bold">Profesor extracurricular *</label>
                        <select id="profesor_id" name="profesor_id" class="form-control form-control-lg select2 @error('profesor_id') is-invalid @enderror" required>
                            <option value="">Seleccione un profesor extracurricular</option>
                            @if(($profesores ?? collect())->isEmpty())
                                <option value="" disabled>No hay profesores extracurriculares</option>
                            @endif
                            @foreach(($profesores ?? collect()) as $profesor)
                                <option value="{{ $profesor->id }}" {{ (string) old('profesor_id') === (string) $profesor->id ? 'selected' : '' }}>
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
                        <input type="text" id="aula" name="aula" value="{{ old('aula') }}" class="form-control form-control-lg @error('aula') is-invalid @enderror" placeholder="Opcional">
                        @error('aula')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="hora_inicio" class="font-weight-bold">Hora inicio *</label>
                        <input type="time" id="hora_inicio" name="hora_inicio" value="{{ old('hora_inicio') }}" class="form-control form-control-lg @error('hora_inicio') is-invalid @enderror" required>
                        @error('hora_inicio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="hora_fin" class="font-weight-bold">Hora fin *</label>
                        <input type="time" id="hora_fin" name="hora_fin" value="{{ old('hora_fin') }}" class="form-control form-control-lg @error('hora_fin') is-invalid @enderror" required>
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
                    $diaPorDefecto = old('dia_semana', \Carbon\Carbon::now('America/Caracas')->isoWeekday());
                @endphp
                <div class="form-group">
                    <label for="dia_semana" class="font-weight-bold">Día de la semana *</label>
                    <select id="dia_semana" name="dia_semana" class="form-control form-control-lg @error('dia_semana') is-invalid @enderror" required>
                        <option value="">Seleccione un día</option>
                        @foreach($diasSemana as $valor => $label)
                            <option value="{{ $valor }}" {{ (string) $diaPorDefecto === (string) $valor ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('dia_semana')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="descripcion" class="font-weight-bold">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3" class="form-control @error('descripcion') is-invalid @enderror" placeholder="Opcional">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="estudiantes_id" class="font-weight-bold">Estudiantes (mixtos) *</label>
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
                                    <option value="{{ $e->id }}" {{ in_array($e->id, old('estudiantes_id', [])) ? 'selected' : '' }}>
                                        {{ $e->apellidos }} {{ $e->nombres }} ({{ $e->codigo_estudiante }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('estudiantes_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="small text-muted mt-1">Puedes buscar por nombre, apellido o código. Selecciona varios.</div>
                </div>

                <div class="d-flex flex-wrap justify-content-start mt-4">
                    <button type="submit" class="btn btn-primary btn-lg mr-2 mb-2">
                        <i class="fas fa-save mr-1"></i> Guardar
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
            placeholder: 'Seleccione un profesor extracurricular'
        });
    });
</script>
@endpush
