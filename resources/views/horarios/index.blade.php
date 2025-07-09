@extends('adminlte::page')

@section('title', 'Horarios de Clases')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Horarios de Clases</h1>
    <div class="btn-group">
        <a href="{{ route('horarios.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Nuevo Horario
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
                <table id="horariosTable" class="table table-hover table-striped datatable">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th>Día</th>
                            <th>Hora Inicio</th>
                            <th>Hora Fin</th>
                            <th>Aula</th>
                            <th>Profesor</th>
                            <th>Materia</th>
                            <th>Grado</th>
                            <th>Sección</th>
                            <th class="text-center align-middle">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @isset($horarios)
                            @if($horarios->isEmpty())
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-clock fa-3x mb-3"></i>
                                            <p class="mb-0">¡No hay horarios registrados!</p>
                                            <p class="small text-muted mb-3">No se han asignado horarios para las clases aún.</p>
                                            <a href="{{ route('horarios.create') }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus mr-1"></i> Agregar Horario
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach($horarios as $horario)
                                    <tr>
                                        <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                        <td class="align-middle">{{ $horario->dia }}</td>
                                        <td class="align-middle">{{ $horario->hora_inicio }}</td>
                                        <td class="align-middle">{{ $horario->hora_fin }}</td>
                                        <td class="align-middle">{{ $horario->aula }}</td>
                                        <td class="align-middle">{{ $horario->asignacion->profesor->user->name }}</td>
                                        <td class="align-middle">{{ $horario->asignacion->materia->nombre }}</td>
                                        <td class="align-middle">{{ $horario->grado->seccion->grado->nombre ?? 'N/A' }}</td>
                                        <td class="align-middle">{{ $horario->grado->seccion->nombre }}</td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center">
                                                <a href="{{ route('horarios.show', ['horario' => $horario->id]) }}" 
                                                   class="btn btn-sm btn-light mx-1 border"
                                                   data-toggle="tooltip" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                                <a href="{{ route('horarios.edit', ['horario' => $horario->id]) }}" 
                                                   class="btn btn-sm btn-light mx-1 border"
                                                   data-toggle="tooltip" 
                                                   title="Editar">
                                                    <i class="fas fa-edit text-warning"></i>
                                                </a>
                                                <form action="{{ route('horarios.destroy', ['horario' => $horario->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-light mx-1 border"
                                                            data-toggle="tooltip" 
                                                            title="Eliminar"
                                                            onclick="event.preventDefault(); eliminarRegistro(this)" 
                                                            data-nombre="{{ $horario->asignacion->profesor->user->name }}"
                                                            data-tipo="el horario">
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
