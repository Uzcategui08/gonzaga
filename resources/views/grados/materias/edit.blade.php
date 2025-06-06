@extends('adminlte::page')

@section('title', 'Editar Asignación de Materias')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Editar Asignación de Materias para {{ $grado->nombre }}</h1>
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
            <form action="{{ route('grado-materia.update', $grado) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="container-fluid">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
                        @foreach($materias as $materia)
                            <div class="col mb-4">
                                <div class="card h-100 transition-all {{ $grado->materias->contains($materia->id) ? 'border-primary' : 'border-light' }} shadow-lg hover:shadow-xl"> <!-- Aumenté la sombra y añadí efecto hover -->
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="materia_ids[]" 
                                                   value="{{ $materia->id }}"
                                                   id="materia{{ $materia->id }}"
                                                   {{ $grado->materias->contains($materia->id) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="materia{{ $materia->id }}">
                                                <div class="d-flex flex-column">
                                                    <h5 class="card-title mb-3">{{ $materia->nombre }}</h5>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge badge-pill py-1 px-2 
                                                            @if($materia->nivel === 'primaria') badge-primary
                                                            @else badge-info
                                                            @endif">
                                                            {{ ucfirst($materia->nivel) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-start">
                        <button type="submit" class="btn btn-warning btn-lg mr-2">
                            <i class="fas fa-save mr-1"></i> Actualizar
                        </button>
                        <a href="{{ route('grado-materia.show', $grado) }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .card {
        transition: all 0.3s ease;
        border-radius: 0.5rem;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }
    
    .shadow-lg {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08) !important;
    }
    
    .hover\:shadow-xl:hover {
        box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1) !important;
    }
    
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 150ms;
    }
    
    .form-check-input {
        width: 1.2em;
        height: 1.2em;
        margin-top: 0.2em;
    }
    
    .form-check-label {
        width: 100%;
        cursor: pointer;
    }
    
    .badge {
        font-size: 0.8rem;
        font-weight: 500;
    }
</style>
@endsection