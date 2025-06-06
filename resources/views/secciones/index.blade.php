@extends('adminlte::page')

@section('title', 'Gestión de Secciones')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Gestión de Secciones</h1>
        <a href="{{ route('secciones.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Nueva Sección
        </a>
    </div>
@endsection

@section('content')
    <div class="container-fluid px-0">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <h3 class="card-title mb-0">
                    <i class="fas fa-list-ol text-primary mr-2"></i> Listado
                </h3>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive table-responsive-md rounded-lg" style="margin: 0.5rem;">
                    <table class="table table-hover mb-0 datatable" id="secciones-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center py-3" style="width: 5%; min-width: 50px">#</th>
                                <th class="py-3" style="width: 30%; min-width: 150px">Nombre de la Sección</th>
                                <th class="py-3" style="width: 30%; min-width: 150px">Grado</th>
                                <th class="text-center py-3" style="width: 35%; min-width: 200px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!$secciones->isEmpty())
                                @foreach($secciones as $seccion)
                                    <tr class="border-bottom">
                                        <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                        <td class="align-middle">
                                            <span class="font-weight-semibold text-dark">{{ $seccion->nombre }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-pill py-2 px-3 
                                                @if($seccion->grado->nivel == 'Primaria') badge-primary
                                                @elseif($seccion->grado->nivel == 'Secundaria') badge-info
                                                @else badge-secondary
                                                @endif">
                                                {{ $seccion->grado->nombre }} ({{ $seccion->grado->nivel }})
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center">
                                                <a href="{{ route('secciones.show', $seccion) }}" 
                                                   class="btn btn-sm btn-light mx-1 border"
                                                   data-toggle="tooltip" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                                <a href="{{ route('secciones.edit', $seccion) }}" 
                                                   class="btn btn-sm btn-light mx-1 border"
                                                   data-toggle="tooltip" 
                                                   title="Editar">
                                                    <i class="fas fa-edit text-warning"></i>
                                                </a>
                                                <form action="{{ route('secciones.destroy', $seccion) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-light mx-1 border"
                                                            data-toggle="tooltip" 
                                                            title="Eliminar"
                                                            onclick="event.preventDefault(); eliminarRegistro(this)"
                                                            data-nombre="{{ $seccion->nombre }}"
                                                            data-tipo="la sección">
                                                        <i class="fas fa-trash-alt text-danger"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
