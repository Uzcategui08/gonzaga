@extends('adminlte::page')

@section('title', 'Nuevo Justificativo')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">
        Nuevo Justificativo
    </h1>
</div>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-plus-circle text-primary mr-2"></i>
            </h3>
        </div>
        <div class="card-body p-3">
            <form action="{{ route('justificativos.store') }}" method="POST">
            @csrf
            <input type="hidden" name="estudiante_id" value="{{ $estudiante->id }}">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Estudiante</label>
                        <input type="text" class="form-control form-control-lg" value="{{ $estudiante->nombres }} {{ $estudiante->apellidos }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Tipo</label>
                        <select name="tipo" class="form-control form-control-lg" required>
                            <option value="salud">Salud</option>
                            <option value="familiar">Familiar</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control form-control-lg" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control form-control-lg" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="font-weight-bold text-gray-700">Motivo</label>
                <textarea name="motivo" class="form-control form-control-lg" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label class="font-weight-bold text-gray-700">Observaciones</label>
                <textarea name="observaciones" class="form-control form-control-lg" rows="3"></textarea>
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="aprobado" name="aprobado" value="1" checked>
                    <label class="custom-control-label" for="aprobado">Aprobar justificativo</label>
                </div>
            </div>

            <div class="form-group">
                <div class="d-flex justify-content-start">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                    <a href="{{ route('justificativos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
