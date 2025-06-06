@extends('adminlte::page')

@section('title', 'Crear Materia')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Crear Materia</h1>
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
            <form action="{{ route('materias.store') }}" method="POST">
                @csrf

                <div class="form-group mb-4">
                    <label for="nombre" class="font-weight-bold text-gray-700">Nombre de la Materia</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-book"></i>
                            </span>
                        </div>
                        <input type="text" name="nombre" id="nombre" required
                               class="form-control form-control-lg @error('nombre') is-invalid @enderror" 
                               placeholder="Ingrese el nombre de la materia" value="{{ old('nombre') }}">
                        @error('nombre')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="nivel" class="font-weight-bold text-gray-700">Nivel Educativo</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-graduation-cap"></i>
                            </span>
                        </div>
                        <select name="nivel" id="nivel" required 
                                class="form-control form-control-lg @error('nivel') is-invalid @enderror">
                            <option value="primaria">Primaria</option>
                            <option value="secundaria">Secundaria</option>
                        </select>
                        @error('nivel')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-start">
                        <button type="submit" class="btn btn-primary btn-lg mr-2">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </button>
                        <a href="{{ route('materias.index') }}" class="btn btn-secondary btn-lg ml-2">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
