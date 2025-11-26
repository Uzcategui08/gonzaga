@extends('adminlte::page')

@section('title', 'Estudiantes')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Estudiantes</h1>
    <div class="btn-group">
        <a href="{{ route('estudiantes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Nuevo Estudiante
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
        @php
            $niveles = $niveles ?? collect();
            $nivelSeleccionado = $nivelSeleccionado ?? request('nivel');
        @endphp
        <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-users text-primary mr-2"></i>Listado
                </h3>
                <form method="GET" action="{{ route('estudiantes.index') }}" class="form-inline mt-3 mt-md-0">
                    <label for="nivel" class="mr-2 mb-0 font-weight-semibold">Nivel</label>
                    <select name="nivel" id="nivel" class="form-control mr-2" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        @foreach($niveles as $nivel)
                            <option value="{{ $nivel }}" {{ $nivelSeleccionado === $nivel ? 'selected' : '' }}>{{ $nivel }}</option>
                        @endforeach
                    </select>
                    @if($nivelSeleccionado)
                        <a href="{{ route('estudiantes.index') }}" class="btn btn-link">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="estudiantesTable" class="table table-hover table-striped datatable">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th>Código</th>
                            <th>Nombre Completo</th>
                            <th>Fecha Nac.</th>
                            <th>Género</th>
                            <th>Estado</th>
                            <th>Grado</th>
                            <th>Sección</th>
                            <th>Fecha Ingreso</th>
                            <th class="text-center align-middle">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @isset($estudiantes)
                            @if($estudiantes->isEmpty())
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-users fa-3x mb-3"></i>
                                            <p class="mb-0">¡No hay estudiantes registrados!</p>
                                            <p class="small text-muted mb-3">No se han registrado estudiantes aún.</p>
                                            <a href="{{ route('estudiantes.create') }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus mr-1"></i> Agregar Estudiante
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach($estudiantes as $estudiante)
                                    <tr>
                                        <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                        <td class="align-middle">{{ $estudiante->codigo_estudiante }}</td>
                                        <td class="align-middle">
                                            {{ $estudiante->apellidos }}, {{ $estudiante->nombres }}
                                        </td>
                                        <td class="align-middle">{{ $estudiante->fecha_nacimiento }}</td>
                                        <td class="align-middle">{{ $estudiante->genero }}</td>
                                        <td class="align-middle">
                                            <span class="badge badge-pill py-2 px-3 
                                                @if($estudiante->estado === 'activo') badge-success
                                                @elseif($estudiante->estado === 'inactivo') badge-warning
                                                @else badge-info
                                                @endif">
                                                {{ ucfirst($estudiante->estado) }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            {{ $estudiante->seccion->grado->nombre }}
                                        </td>
                                        <td class="align-middle">{{ $estudiante->seccion->nombre }}</td>
                                        <td class="align-middle">{{ $estudiante->fecha_ingreso }}</td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center">
                                                <a href="{{ route('estudiantes.show', ['estudiante' => $estudiante->id]) }}" 
                                                   class="btn btn-sm btn-light mx-1 border"
                                                   data-toggle="tooltip" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                                <a href="{{ route('estudiantes.edit', ['estudiante' => $estudiante->id]) }}" 
                                                   class="btn btn-sm btn-light mx-1 border"
                                                   data-toggle="tooltip" 
                                                   title="Editar">
                                                    <i class="fas fa-edit text-warning"></i>
                                                </a>
                                                <form action="{{ route('estudiantes.destroy', ['estudiante' => $estudiante->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-light mx-1 border"
                                                            data-toggle="tooltip" 
                                                            title="Eliminar"
                                                            onclick="event.preventDefault(); eliminarRegistro(this)" 
                                                            data-nombre="{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}"
                                                            data-tipo="el estudiante">
                                                        <i class="fas fa-trash-alt text-danger"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endisset
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
