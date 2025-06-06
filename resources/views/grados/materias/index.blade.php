@extends('adminlte::page')

@section('title', 'Grados y Materias')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Grados y Materias</h1>
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
            @if($grados->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No hay grados registrados</p>
                </div>
            @else
                <div class="table-responsive-md rounded-lg" style="margin: 0.5rem;">
                    <table class="table table-hover mb-0 datatable">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center py-3" style="width: 5%; min-width: 50px">#</th>
                                <th class="py-3" style="width: 45%">Grado</th>
                                <th class="text-center py-3" style="width: 30%">Nivel Educativo</th>
                                <th class="text-center py-3" style="width: 20%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grados as $grado)
                                <tr>
                                    <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                    <td class="align-middle">
                                        <span class="font-weight-semibold text-dark">{{ $grado->nombre }}</span>
                                    </td>
                                    <td class="align-middle text-center">
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
                                                <i class="fas fa-eye text-info"></i>
                                            </a>
                                            <a href="{{ route('grado-materia.edit', $grado) }}" 
                                               class="btn btn-sm btn-light mx-1 border"
                                               data-toggle="tooltip" 
                                               title="Editar Asignación">
                                                <i class="fas fa-edit text-warning"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
