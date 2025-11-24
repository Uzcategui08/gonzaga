@extends('adminlte::page')

@section('title', 'Editar Sección')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Editar Sección</h1>
    <a href="{{ route('secciones.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-edit text-warning mr-2"></i>Editar Sección
            </h3>
        </div>

        <div class="card-body p-3">
            <form action="{{ route('secciones.update', $seccion) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-4">
                    <label for="nombre" class="font-weight-bold text-gray-700">Nombre de la Sección</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-book"></i>
                            </span>
                        </div>
                        <input type="text" name="nombre" id="nombre" value="{{ $seccion->nombre }}" required
                               class="form-control form-control-lg" 
                               placeholder="Ingrese el nombre de la sección">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="grado_id" class="font-weight-bold text-gray-700">Grado</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-graduation-cap"></i>
                            </span>
                        </div>
                        <select name="grado_id" id="grado_id" class="form-control form-control-lg" required>
                            <option value="">Seleccione un grado</option>
                            @foreach($grados as $grado)
                            <option value="{{ $grado->id }}" 
                                    data-nivel="{{ $grado->nivel }}"
                                    {{ $seccion->grado_id == $grado->id ? 'selected' : '' }}>
                                {{ $grado->nombre }} ({{ $grado->nivel }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="titular_profesor_id" class="font-weight-bold text-gray-700">Profesor titular (opcional)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </span>
                        </div>
                        <select name="titular_profesor_id" id="titular_profesor_id" class="form-control form-control-lg">
                            <option value="">Sin titular asignado</option>
                            @isset($profesores)
                                @foreach($profesores as $profesor)
                                    <option value="{{ $profesor->id }}" {{ $seccion->titular_profesor_id == $profesor->id ? 'selected' : '' }}>
                                        {{ $profesor->user->name }}
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-save mr-1"></i> Actualizar Sección
                    </button>
                    <a href="{{ route('secciones.index') }}" class="btn btn-secondary btn-lg ml-2">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

