@extends('adminlte::page')

@section('title', 'Gestión de Materias')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Gestión de Materias</h1>
    <a href="{{ route('materias.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Nueva Materia
    </a>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-list-ol text-primary mr-2"></i>Listado
            </h3>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive-md rounded-lg" style="margin: 0.5rem;">
                <table class="table table-hover mb-0 datatable">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center py-3" style="width: 5%; min-width: 50px">#</th>
                            <th class="py-3" style="width: 55%; min-width: 200px">Nombre de la Materia</th>
                            <th class="py-3" style="width: 20%; min-width: 150px">Nivel</th>
                            <th class="text-center py-3" style="width: 20%; min-width: 180px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!$materias->isEmpty())
                            @foreach($materias as $materia)
                                <tr class="border-bottom">
                                    <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                    <td class="align-middle">
                                        <span class="font-weight-semibold text-dark">{{ $materia->nombre }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-pill py-2 px-3 
                                                @if($materia->nivel === 'primaria') badge-primary
                                                @else badge-info
                                                @endif">
                                            {{ ucfirst($materia->nivel) }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center">
                                            <a href="{{ route('materias.show', $materia) }}" 
                                               class="btn btn-sm btn-light mx-1 border"
                                               data-toggle="tooltip" 
                                               title="Ver detalles">
                                                <i class="fas fa-eye text-info"></i>
                                            </a>
                                            <a href="{{ route('materias.edit', $materia) }}" 
                                               class="btn btn-sm btn-light mx-1 border"
                                               data-toggle="tooltip" 
                                               title="Editar">
                                                <i class="fas fa-edit text-warning"></i>
                                            </a>
                                            <form action="{{ route('materias.destroy', $materia) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-light mx-1 border"
                                                        data-toggle="tooltip" 
                                                        title="Eliminar"
                                                        onclick="event.preventDefault(); eliminarRegistro(this)" 
                                                        data-nombre="{{ $materia->nombre }}" 
                                                        data-tipo="la materia">
                                                    <i class="fas fa-trash-alt text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-book fa-3x mb-3"></i>
                                        <p class="mb-0">No hay materias registradas</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
