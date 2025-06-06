@extends('adminlte::page')

@section('title', 'Crear Sección')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Crear Sección</h1>
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
                <i class="fas fa-plus-circle text-primary mr-2"></i>Nueva Sección
            </h3>
        </div>

        <div class="card-body p-3">
            <form action="{{ route('secciones.store') }}" method="POST">
                @csrf

                <div class="form-group mb-4">
                    <label for="nombre" class="font-weight-bold text-gray-700">Nombre de la Sección</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-book"></i>
                            </span>
                        </div>
                        <input type="text" name="nombre" id="nombre" required
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
                                    data-nivel="{{ $grado->nivel }}">
                                {{ $grado->nombre }} ({{ $grado->nivel }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save mr-1"></i> Guardar Sección
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

@section('css')
<style>
    :root {
        --primary-color: #3490dc;
        --secondary-color: #6c757d;
        --success-color: #38c172;
        --info-color: #6cb2eb;
        --warning-color: #ffed4a;
        --danger-color: #e3342f;
    }

    .card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
    }

    .card-header {
        padding: 1.25rem 1.5rem;
        background-color: #f8fafc;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d3748;
    }

    .form-control-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border-radius: 0.375rem;
        border: 1px solid #e2e8f0;
    }

    .form-control-lg:focus {
        border-color: #4299e1;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.25);
    }

    .input-group-text {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem 0 0 0.375rem;
    }

    .input-group {
        border-radius: 0.375rem;
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        border-radius: 0.375rem;
    }

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-secondary {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
    }

    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .card-title {
            margin-bottom: 0.5rem;
        }
    }
</style>
@endsection
