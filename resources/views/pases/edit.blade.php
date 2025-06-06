@extends('adminlte::page')

@section('title', 'Editar Pase')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Editar Pase</h1>
    <a href="{{ route('pases.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver al listado
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-edit text-warning mr-2"></i>Editar Pase
            </h3>
        </div>

        <div class="card-body p-3">
            <form action="{{ route('pases.update', $pase) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-4">
                    <label for="estudiante" class="font-weight-bold text-gray-700">Estudiante</label>
                    <input type="text" name="estudiante" id="estudiante" 
                           value="{{ $pase->estudiante->nombres }} {{ $pase->estudiante->apellidos }}"
                           class="form-control form-control-lg" readonly>
                </div>

                <div class="form-group mb-4">
                    <label for="motivo" class="font-weight-bold text-gray-700">Motivo del Pase</label>
                    <input type="text" name="motivo" id="motivo" 
                           value="{{ $pase->motivo }}"
                           class="form-control form-control-lg" 
                           placeholder="Ingrese el motivo del pase">
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label for="fecha" class="font-weight-bold text-gray-700">Fecha</label>
                            <input type="date" name="fecha" id="fecha" 
                                   value="{{ $pase->fecha }}"
                                   class="form-control form-control-lg">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label for="hora_llegada" class="font-weight-bold text-gray-700">Hora de Llegada</label>
                            <input type="time" name="hora_llegada" id="hora_llegada" 
                                   value="{{ $pase->hora_llegada }}"
                                   class="form-control form-control-lg">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="observaciones" class="font-weight-bold text-gray-700">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="3" 
                              class="form-control form-control-lg" 
                              placeholder="Observaciones adicionales">{{ $pase->observaciones }}</textarea>
                </div>

                <div class="form-group mb-4">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="aprobado" name="aprobado" value="1" {{ $pase->aprobado ? 'checked' : '' }}>
                        <label class="custom-control-label" for="aprobado">Aprobar pase</label>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-start">
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="fas fa-save mr-2"></i>Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
