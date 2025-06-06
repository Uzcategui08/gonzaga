@extends('adminlte::page')

@section('title', 'Gestión de Grados')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Gestión de Grados</h1>
    <a href="{{ route('grados.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Nuevo Grado
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
                <table class="table table-hover mb-0 datatable" id="grados-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center py-3" style="width: 5%; min-width: 50px">#</th>
                            <th class="py-3" style="width: 45%; min-width: 200px">Nombre del Grado</th>
                            <th class="py-3" style="width: 30%; min-width: 150px">Nivel Educativo</th>
                            <th class="text-center py-3" style="width: 20%; min-width: 180px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grados as $grado)
                        <tr class="border-bottom">
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle">
                                <span class="font-weight-semibold text-dark">{{ $grado->nombre }}</span>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-pill py-2 px-3 
                                    @if($grado->nivel == 'Primaria') badge-primary
                                    @elseif($grado->nivel == 'Secundaria') badge-info
                                    @else badge-secondary
                                    @endif">
                                    {{ $grado->nivel }}
                                </span>
                            </td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('grado-materia.show', $grado) }}" 
                                       class="btn btn-sm btn-light mx-1 border"
                                       data-toggle="tooltip" 
                                       title="Ver Materias">
                                        <i class="fas fa-book text-primary"></i>
                                    </a>
                                    <a href="{{ route('grados.show', $grado) }}" 
                                       class="btn btn-sm btn-light mx-1 border"
                                       data-toggle="tooltip" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye text-info"></i>
                                    </a>
                                    <a href="{{ route('grados.edit', $grado) }}" 
                                       class="btn btn-sm btn-light mx-1 border"
                                       data-toggle="tooltip" 
                                       title="Editar">
                                        <i class="fas fa-edit text-warning"></i>
                                    </a>
                                    <form action="{{ route('grados.destroy', $grado) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-light mx-1 border"
                                                data-toggle="tooltip" 
                                                title="Eliminar"
                                                onclick="event.preventDefault(); eliminarRegistro(this)" 
                                                data-nombre="{{ $grado->nombre }}" 
                                                data-tipo="el grado">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection