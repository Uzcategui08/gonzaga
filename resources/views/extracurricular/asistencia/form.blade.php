@extends('adminlte::page')

@section('title', 'Asistencia Extracurricular')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark">Asistencia Extracurricular</h1>
        <div class="text-muted">{{ $clase->nombre }}</div>
    </div>
    <a href="{{ route('extracurricular.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-3">
            <form action="{{ route('extracurricular.asistencia.store', $clase->id) }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="fecha" class="font-weight-bold">Fecha *</label>
                        @if(!empty($soloHoy))
                            <input type="hidden" name="fecha" value="{{ old('fecha', $fecha) }}">
                            <input type="date" id="fecha" value="{{ old('fecha', $fecha) }}" class="form-control form-control-lg" disabled>
                        @else
                            <input type="date" id="fecha" name="fecha" value="{{ old('fecha', $fecha) }}" class="form-control form-control-lg @error('fecha') is-invalid @enderror" required>
                        @endif
                        @error('fecha')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label for="hora_inicio" class="font-weight-bold">Hora inicio</label>
                        <input type="time" id="hora_inicio" name="hora_inicio" value="{{ old('hora_inicio', optional($asistencia)->hora_inicio) }}" class="form-control form-control-lg @error('hora_inicio') is-invalid @enderror">
                        @error('hora_inicio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-4 d-flex align-items-end">
                        @if(empty($soloHoy))
                            <a class="btn btn-outline-primary btn-lg w-100" href="{{ route('extracurricular.asistencia.form', $clase->id) }}">
                                <i class="fas fa-calendar-day mr-1"></i> Hoy
                            </a>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label for="contenido_clase" class="font-weight-bold">Contenido de la clase *</label>
                    <textarea id="contenido_clase" name="contenido_clase" rows="3" class="form-control @error('contenido_clase') is-invalid @enderror" required>{{ old('contenido_clase', optional($asistencia)->contenido_clase) }}</textarea>
                    @error('contenido_clase')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="observacion_general" class="font-weight-bold">Observación general</label>
                    <textarea id="observacion_general" name="observacion_general" rows="2" class="form-control @error('observacion_general') is-invalid @enderror">{{ old('observacion_general', optional($asistencia)->observacion_general) }}</textarea>
                    @error('observacion_general')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h3 class="card-title mb-0">Estudiantes</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="35%">Estudiante</th>
                                        <th width="15%">Estado</th>
                                        <th width="50%">Observación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clase->estudiantes as $estudiante)
                                        @php
                                            $detalle = $pivotPorEstudiante->get($estudiante->id);
                                            $estadoActual = old('estudiantes.' . $estudiante->id . '.estado', $detalle?->pivot?->estado ?? 'A');
                                            $obsActual = old('estudiantes.' . $estudiante->id . '.observacion_individual', $detalle?->pivot?->observacion_individual);
                                            $seccionLabel = $estudiante->seccion?->grado?->nombre && $estudiante->seccion?->nombre
                                                ? ($estudiante->seccion->grado->nombre . ' - ' . $estudiante->seccion->nombre)
                                                : null;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $loop->iteration }}. {{ $estudiante->apellidos }} {{ $estudiante->nombres }}</strong>
                                                @if($seccionLabel)
                                                    <div class="text-muted small">{{ $seccionLabel }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <select class="form-control form-control-sm" name="estudiantes[{{ $estudiante->id }}][estado]" required>
                                                    <option value="A" {{ $estadoActual === 'A' ? 'selected' : '' }}>Asistente</option>
                                                    <option value="I" {{ $estadoActual === 'I' ? 'selected' : '' }}>Inasistente</option>
                                                    <option value="P" {{ $estadoActual === 'P' ? 'selected' : '' }}>Pase</option>
                                                </select>
                                            </td>
                                            <td>
                                                <textarea class="form-control form-control-sm" name="estudiantes[{{ $estudiante->id }}][observacion_individual]" rows="2" placeholder="Opcional">{{ $obsActual }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-start mt-4">
                    <button type="submit" class="btn btn-primary btn-lg mr-2 mb-2">
                        <i class="fas fa-save mr-1"></i> Guardar asistencia
                    </button>
                    <a href="{{ route('extracurricular.index') }}" class="btn btn-secondary btn-lg mb-2">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
