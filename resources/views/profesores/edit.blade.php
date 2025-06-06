@extends('adminlte::page')

@section('title', 'Editar Profesor')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Editar Profesor</h1>
    <a href="{{ route('profesores.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-chalkboard-teacher text-primary mr-2"></i>Información del Profesor
            </h3>
        </div>

        <div class="card-body p-3">
            <form action="{{ route('profesores.update', $profesor) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <!-- Usuario -->
                    <div class="form-group col-md-6">
                        <label for="user_id" class="font-weight-bold text-gray-700">Usuario</label>
                        <select name="user_id" id="user_id" class="form-control form-control-lg @error('user_id') is-invalid @enderror" required disabled>
                            <option value="{{ $profesor->user_id }}">
                                {{ $profesor->user->name }} ({{ $profesor->user->email }})
                            </option>
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Código Profesor -->
                    <div class="form-group col-md-6">
                        <label for="codigo_profesor" class="font-weight-bold text-gray-700">Código de Profesor</label>
                        <input type="text" 
                               name="codigo_profesor" 
                               id="codigo_profesor" 
                               class="form-control form-control-lg @error('codigo_profesor') is-invalid @enderror" 
                               value="{{ old('codigo_profesor', $profesor->codigo_profesor) }}" 
                               required>
                        @error('codigo_profesor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Especialidad -->
                    <div class="form-group col-md-6">
                        <label for="especialidad" class="font-weight-bold text-gray-700">Especialidad</label>
                        <input type="text" 
                               name="especialidad" 
                               id="especialidad" 
                               class="form-control form-control-lg @error('especialidad') is-invalid @enderror" 
                               value="{{ old('especialidad', $profesor->especialidad) }}" 
                               required>
                        @error('especialidad')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tipo de Contrato -->
                    <div class="form-group col-md-6">
                        <label for="tipo_contrato" class="font-weight-bold text-gray-700">Tipo de Contrato</label>
                        <select name="tipo_contrato" id="tipo_contrato" class="form-control form-control-lg @error('tipo_contrato') is-invalid @enderror" required>
                            <option value="">Seleccione el tipo de contrato</option>
                            <option value="titular" {{ old('tipo_contrato', $profesor->tipo_contrato) == 'titular' ? 'selected' : '' }}>Titular</option>
                            <option value="contratado" {{ old('tipo_contrato', $profesor->tipo_contrato) == 'contratado' ? 'selected' : '' }}>Contratado</option>
                            <option value="sustituto" {{ old('tipo_contrato', $profesor->tipo_contrato) == 'sustituto' ? 'selected' : '' }}>Sustituto</option>
                        </select>
                        @error('tipo_contrato')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Fecha de Contratación -->
                    <div class="form-group col-md-6">
                        <label for="fecha_contratacion" class="font-weight-bold text-gray-700">Fecha de Contratación</label>
                        <input type="date" 
                               name="fecha_contratacion" 
                               id="fecha_contratacion" 
                               class="form-control form-control-lg @error('fecha_contratacion') is-invalid @enderror" 
                               value="{{ old('fecha_contratacion', $profesor->fecha_contratacion->format('Y-m-d')) }}" 
                               required>
                        @error('fecha_contratacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-save mr-1"></i> Actualizar Profesor
                    </button>
                    <a href="{{ route('profesores.index') }}" class="btn btn-secondary btn-lg ml-2">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
