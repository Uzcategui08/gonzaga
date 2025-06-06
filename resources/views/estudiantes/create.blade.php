@extends('adminlte::page')

@section('title', 'Crear Estudiante')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Crear Estudiante</h1>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-user-plus text-primary mr-2"></i>
            </h3>
        </div>

        <div class="card-body p-3">
            <form action="{{ route('estudiantes.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user mr-2"></i>Datos Personales
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="codigo_estudiante" class="font-weight-bold text-gray-700">Código de Estudiante</label>
                                        <input type="text" name="codigo_estudiante" id="codigo_estudiante" 
                                               class="form-control @error('codigo_estudiante') is-invalid @enderror" 
                                               value="{{ old('codigo_estudiante') }}" required>
                                        @error('codigo_estudiante')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="fecha_nacimiento" class="font-weight-bold text-gray-700">Fecha de Nacimiento</label>
                                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" 
                                               class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                                               value="{{ old('fecha_nacimiento') }}" required>
                                        @error('fecha_nacimiento')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="genero" class="font-weight-bold text-gray-700">Género</label>
                                        <select name="genero" id="genero" class="form-control @error('genero') is-invalid @enderror" required>
                                            <option value="">Seleccione el género</option>
                                            <option value="M" {{ old('genero') === 'M' ? 'selected' : '' }}>Masculino</option>
                                            <option value="F" {{ old('genero') === 'F' ? 'selected' : '' }}>Femenino</option>
                                        </select>
                                        @error('genero')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="nombres" class="font-weight-bold text-gray-700">Nombres</label>
                                        <input type="text" name="nombres" id="nombres" 
                                               class="form-control @error('nombres') is-invalid @enderror" 
                                               value="{{ old('nombres') }}" required>
                                        @error('nombres')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="apellidos" class="font-weight-bold text-gray-700">Apellidos</label>
                                        <input type="text" name="apellidos" id="apellidos" 
                                               class="form-control @error('apellidos') is-invalid @enderror" 
                                               value="{{ old('apellidos') }}" required>
                                        @error('apellidos')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-graduation-cap mr-2"></i>Datos Académicos
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="seccion_id" class="font-weight-bold text-gray-700">Sección</label>
                                        <select name="seccion_id" id="seccion_id" class="form-control select2 @error('seccion_id') is-invalid @enderror" required>
                                            <option value="">Seleccione una sección</option>
                                            @foreach($secciones as $seccion)
                                                <option value="{{ $seccion->id }}" {{ old('seccion_id') == $seccion->id ? 'selected' : '' }}>
                                                    {{ $seccion->grado->nombre }} - {{ $seccion->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('seccion_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="fecha_ingreso" class="font-weight-bold text-gray-700">Fecha de Ingreso</label>
                                        <input type="date" name="fecha_ingreso" id="fecha_ingreso" 
                                               class="form-control form-control-lg @error('fecha_ingreso') is-invalid @enderror" 
                                               value="{{ old('fecha_ingreso') }}" required>
                                        @error('fecha_ingreso')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-home mr-2"></i>Datos de Contacto
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="direccion" class="font-weight-bold text-gray-700">Dirección</label>
                                    <input type="text" name="direccion" id="direccion" 
                                           class="form-control form-control-lg @error('direccion') is-invalid @enderror" 
                                           value="{{ old('direccion') }}">
                                    @error('direccion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="observaciones" class="font-weight-bold text-gray-700">Observaciones</label>
                                    <textarea name="observaciones" id="observaciones" 
                                              class="form-control @error('observaciones') is-invalid @enderror" 
                                              style="height: calc(2.875rem + 2px);">{{ old('observaciones') }}</textarea>
                                    @error('observaciones')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-phone-alt mr-2"></i>Contacto de Emergencia
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="contacto_emergencia_nombre" class="font-weight-bold text-gray-700">Nombre</label>
                                        <input type="text" name="contacto_emergencia_nombre" id="contacto_emergencia_nombre" 
                                               class="form-control form-control-lg @error('contacto_emergencia_nombre') is-invalid @enderror" 
                                               value="{{ old('contacto_emergencia_nombre') }}" required>
                                        @error('contacto_emergencia_nombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="contacto_emergencia_parentesco" class="font-weight-bold text-gray-700">Parentesco</label>
                                        <input type="text" name="contacto_emergencia_parentesco" id="contacto_emergencia_parentesco" 
                                               class="form-control form-control-lg @error('contacto_emergencia_parentesco') is-invalid @enderror" 
                                               value="{{ old('contacto_emergencia_parentesco') }}" required>
                                        @error('contacto_emergencia_parentesco')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="contacto_emergencia_telefono" class="font-weight-bold text-gray-700">Teléfono</label>
                                    <input type="text" name="contacto_emergencia_telefono" id="contacto_emergencia_telefono" 
                                           class="form-control form-control-lg @error('contacto_emergencia_telefono') is-invalid @enderror" 
                                           value="{{ old('contacto_emergencia_telefono') }}" required>
                                    @error('contacto_emergencia_telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-start">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </button>
                        <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary btn-lg ml-2">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
