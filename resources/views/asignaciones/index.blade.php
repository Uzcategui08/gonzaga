@extends('adminlte::page')

@section('title', 'Asignaciones de Profesores')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Asignaciones de Profesores</h1>
    <div class="btn-group">
        <a href="{{ route('asignaciones.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Nueva Asignación
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-list text-primary mr-2"></i>Listado
            </h3>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover mb-0 datatable" id="asignacionesTable">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-nowrap">#</th>
                            <th>Profesor</th>
                            <th>Materia</th>
                            <th>Nivel</th>
                            <th>Grado</th>
                            <th>Sección</th>
                            <th class="text-center align-middle">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @isset($asignaciones)
                            @if($asignaciones->isEmpty())
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-chalkboard-teacher fa-3x mb-3"></i>
                                            <p class="mb-0">¡No hay asignaciones registradas!</p>
                                            <p class="small text-muted mb-3">No se han asignado profesores a materias y secciones aún.</p>
                                            <a href="{{ route('asignaciones.create') }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus mr-1"></i> Agregar Asignación
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach($asignaciones as $asignacion)
                                    <tr>
                                        <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                        <td class="align-middle">
                                            <span class="font-weight-semibold text-dark">{{ $asignacion->profesor->user->name }}</span>
                                        </td>
                                        <td class="align-middle">
                                            {{ $asignacion->materia->nombre }}
                                        </td>
                                        <td class="align-middle">
                                            {{ ucfirst($asignacion->materia->nivel) }}
                                        </td>
                                        <td class="align-middle">
                                            {{ $asignacion->gradoThroughSeccion->grado->nombre ?? 'N/A' }}
                                        </td>
                                        <td class="align-middle">
                                            {{ $asignacion->seccion->nombre }}
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center">
                                                <a href="{{ route('asignaciones.show', ['asignacion' => $asignacion->id]) }}" 
                                                   class="btn btn-sm btn-light mx-1 border"
                                                   data-toggle="tooltip" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                                <a href="{{ route('asignaciones.edit', ['asignacion' => $asignacion->id]) }}" 
                                                   class="btn btn-sm btn-light mx-1 border"
                                                   data-toggle="tooltip" 
                                                   title="Editar">
                                                    <i class="fas fa-edit text-warning"></i>
                                                </a>
                                                <form action="{{ route('asignaciones.destroy', ['asignacion' => $asignacion->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-light mx-1 border"
                                                            data-toggle="tooltip" 
                                                            title="Eliminar"
                                                            onclick="event.preventDefault(); eliminarRegistro(this)" 
                                                            data-nombre="{{ $asignacion->profesor->user->name }}" 
                                                            data-tipo="la asignación">
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
