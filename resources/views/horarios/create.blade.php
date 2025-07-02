@extends('adminlte::page')

@section('title', 'Crear Horario')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Crear Horario</h1>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-plus-circle text-primary mr-2"></i>
            </h3>
        </div>

        <div class="card-body p-3">
            <form action="{{ route('horarios.store') }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="asignacion_id" class="font-weight-bold text-gray-700">Asignación</label>
                        <select name="asignacion_id" id="asignacion_id" class="form-control form-control-lg select2 @error('asignacion_id') is-invalid @enderror" required>
                            <option value="">Seleccione una asignación</option>
                            @foreach($asignaciones as $asignacion)
                                <option value="{{ $asignacion->id }}">
                                    {{ $asignacion->profesor->user->name }} - 
                                    {{ $asignacion->materia->nombre }} - 
                                    {{ $asignacion->materia->grado->nombre }} -
                                    {{ $asignacion->seccion->nombre }} -
                                    ({{ $asignacion->materia->nivel }})
                                </option>
                            @endforeach
                        </select>
                        @error('asignacion_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label for="dia" class="font-weight-bold text-gray-700">Día</label>
                        <select name="dia" id="dia" class="form-control form-control-lg @error('dia') is-invalid @enderror" required>
                            <option value="">Seleccione un día</option>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                            <option value="Sábado">Sábado</option>
                        </select>
                        @error('dia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label for="hora_inicio" class="font-weight-bold text-gray-700">Hora Inicio</label>
                        <input type="time" name="hora_inicio" id="hora_inicio" 
                               class="form-control form-control-lg @error('hora_inicio') is-invalid @enderror" 
                               required>
                        @error('hora_inicio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label for="hora_fin" class="font-weight-bold text-gray-700">Hora Fin</label>
                        <input type="time" name="hora_fin" id="hora_fin" 
                               class="form-control form-control-lg @error('hora_fin') is-invalid @enderror" 
                               required>
                        @error('hora_fin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label for="aula" class="font-weight-bold text-gray-700">Aula</label>
                        <input type="text" name="aula" id="aula" 
                               class="form-control form-control-lg @error('aula') is-invalid @enderror" 
                               placeholder="Ej: A101" required>
                        @error('aula')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-start mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save mr-1"></i> Guardar Horario
                        </button>
                        </a>
                        <a href="{{ route('horarios.index') }}" class="btn btn-secondary btn-lg ml-2">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
