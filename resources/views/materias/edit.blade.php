@extends('adminlte::page')

@section('title', 'Editar Materia')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Editar Materia</h1>
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
            <form action="{{ route('materias.update', $materia) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-4">
                    <label for="nombre" class="font-weight-bold text-gray-700">Nombre de la Materia</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-book"></i>
                            </span>
                        </div>
                        <input type="text" name="nombre" id="nombre" value="{{ $materia->nombre }}" required
                               class="form-control form-control-lg" 
                               placeholder="Ingrese el nombre de la materia">
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
                                class="form-control form-control-lg">
                            <option value="primaria" {{ $materia->nivel === 'primaria' ? 'selected' : '' }}>Primaria</option>
                            <option value="secundaria" {{ $materia->nivel === 'secundaria' ? 'selected' : '' }}>Secundaria</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-start">
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="fas fa-save mr-1"></i> Actualizar Materia
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
@endsection

