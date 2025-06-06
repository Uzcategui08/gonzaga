@extends('adminlte::page')

@section('title', 'Editar Asignación')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Editar Asignación</h1>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-edit text-warning mr-2"></i>
            </h3>
        </div>

        <div class="card-body p-3">
            <form action="{{ route('asignaciones.update', ['asignacion' => $asignacion->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="profesor_id" class="font-weight-bold text-gray-700">Profesor</label>
                        <select name="profesor_id" id="profesor_id" class="form-control form-control-lg select2 @error('profesor_id') is-invalid @enderror" required>
                            <option value="">Seleccione un profesor</option>
                            @foreach($profesores as $profesor)
                                <option value="{{ $profesor->id }}" {{ $asignacion->profesor_id == $profesor->id ? 'selected' : '' }}>
                                    {{ $profesor->user->name }} ({{ $profesor->codigo_profesor }})
                                </option>
                            @endforeach
                        </select>
                        @error('profesor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="materia_id" class="font-weight-bold text-gray-700">Materia</label>
                        <select name="materia_id" id="materia_id" class="form-control form-control-lg select2 @error('materia_id') is-invalid @enderror" required>
                            <option value="">Seleccione una materia</option>
                            @foreach($materias as $materia)
                                <option value="{{ $materia->id }}" {{ $asignacion->materia_id == $materia->id ? 'selected' : '' }}>
                                    {{ $materia->nombre }} - {{ $materia->nivel }}
                                </option>
                            @endforeach
                        </select>
                        @error('materia_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="seccion_id" class="font-weight-bold text-gray-700">Sección</label>
                        <select name="seccion_id" id="seccion_id" class="form-control form-control-lg select2 @error('seccion_id') is-invalid @enderror" required>
                            <option value="">Seleccione una sección</option>
                            @foreach($secciones as $seccion)
                                <option value="{{ $seccion->id }}" {{ $asignacion->seccion_id == $seccion->id ? 'selected' : '' }}>
                                    {{ $seccion->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('seccion_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <div class="d-flex flex-wrap justify-content-start">
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="fas fa-save mr-1"></i> Actualizar
                        </button>
                        </a>
                        <a href="{{ route('asignaciones.index') }}" class="btn btn-secondary btn-lg ml-2">
                            <i class="fas fa-times mr-1"></i> Cancelar  
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
