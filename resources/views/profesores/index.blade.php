@extends('adminlte::page')

@section('title', 'Gestión de Profesores')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Gestión de Profesores</h1>
    <a href="{{ route('profesores.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Nuevo Profesor
    </a>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-chalkboard-teacher text-primary mr-2"></i>Listado
            </h3>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive-md rounded-lg" style="margin: 0.5rem;">
                <table class="table table-hover mb-0 datatable" id="profesores-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center py-3" style="width: 5%; min-width: 50px">#</th>
                            <th class="py-3" style="width: 20%; min-width: 150px">Nombre</th>
                            <th class="py-3" style="width: 20%; min-width: 150px">Código</th>
                            <th class="py-3" style="width: 20%; min-width: 150px">Especialidad</th>
                            <th class="py-3" style="width: 15%; min-width: 120px">Tipo de Contrato</th>
                            <th class="py-3" style="width: 15%; min-width: 120px">Fecha Contratación</th>
                            <th class="text-center py-3" style="width: 15%; min-width: 180px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @isset($profesores)
                            @if($profesores->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-chalkboard-teacher fa-3x mb-3"></i>
                                            <p class="mb-0">No hay profesores registrados</p>
                                            <a href="{{ route('profesores.create') }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus mr-1"></i> Agregar Profesor
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach($profesores as $profesor)
                                    <tr>
                                        <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                        <td class="align-middle">
                                            <span class="font-weight-semibold text-dark">{{ $profesor->user->name }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-pill py-2 px-3 badge-primary">
                                                {{ $profesor->codigo_profesor }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            {{ $profesor->especialidad }}
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge badge-pill py-2 px-3 
                                                @if($profesor->tipo_contrato == 'titular') badge-success
                                                @elseif($profesor->tipo_contrato == 'contratado') badge-info
                                                @else badge-warning
                                                @endif">
                                                {{ $profesor->tipo_contrato }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            {{ $profesor->fecha_contratacion->format('d/m/Y') }}
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center">
                                                <a href="{{ route('profesores.show', ['profesor' => $profesor->id]) }}" 
                                                   class="btn btn-sm btn-light mx-1 border"
                                                   data-toggle="tooltip" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                                <a href="{{ route('profesores.edit', ['profesor' => $profesor->id]) }}" 
                                                   class="btn btn-sm btn-light mx-1 border"
                                                   data-toggle="tooltip" 
                                                   title="Editar">
                                                    <i class="fas fa-edit text-warning"></i>
                                                </a>
                                                <form action="{{ route('profesores.destroy', ['profesor' => $profesor->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-light mx-1 border"
                                                            data-toggle="tooltip" 
                                                            title="Eliminar"
                                                            onclick="event.preventDefault(); eliminarRegistro(this)" 
                                                            data-nombre="{{ $profesor->user->name }}" 
                                                            data-tipo="el profesor">
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
@endsection
