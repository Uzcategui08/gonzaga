@extends('adminlte::page')

@section('title', 'Editar Grado')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Editar Grado</h1>
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
            <form action="{{ route('grados.update', $grado) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-4">
                    <label for="nombre" class="font-weight-bold text-gray-700">Nombre del Grado</label>
                    <input type="text" name="nombre" id="nombre" value="{{ $grado->nombre }}" required
                           class="form-control form-control-lg" 
                           placeholder="Ingrese el nombre del grado">
                </div>

                <div class="form-group mb-4">
                    <label for="nivel" class="font-weight-bold text-gray-700">Nivel Educativo</label>
                    <select name="nivel" id="nivel" class="form-control form-control-lg" required>
                        <option value="">Seleccione un nivel</option>
                        <option value="Primaria" {{ $grado->nivel == 'Primaria' ? 'selected' : '' }}>Primaria</option>
                        <option value="Secundaria" {{ $grado->nivel == 'Secundaria' ? 'selected' : '' }}>Secundaria</option>
                        <option value="Otro" {{ $grado->nivel == 'Otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>

                <div class="mt-6">
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-save mr-1"></i> Actualizar Grado
                    </button>
                    <a href="{{ route('grados.index') }}" class="btn btn-secondary btn-lg ml-2">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
