@extends('adminlte::page')

@section('title', 'Justificativos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Justificativos</h1>
        <a href="{{ route('justificativos.create', ['estudiante' => request()->query('estudiante')]) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Justificativo
        </a>
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
            <div class="table-responsive-md rounded-lg" style="margin: 0.5rem;">
                <table class="table table-hover mb-0 datatable" id="justificativos-table" style="width:100%">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-nowrap">#</th>
                            <th class="py-3">Grado - Sección</th>
                            <th class="py-3">Estudiante</th>
                            <th class="py-3">Fecha Inicio</th>
                            <th class="py-3">Fecha Fin</th>
                            <th class="py-3">Tipo</th>
                            <th class="py-3">Motivo</th>
                            <th class="text-center py-3">Aprobado</th>
                            <th class="text-center py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($justificativos as $justificativo)
                            <tr class="border-bottom">
        <td class="text-nowrap align-middle"></td>
      
                                    <td class="align-middle">
            {{ $justificativo->estudiante->seccion->grado->nombre ?? '' }} - {{ $justificativo->estudiante->seccion->nombre ?? '' }}
        </td>
        <td class="align-middle">
            <span class="font-weight-semibold text-dark">
                {{ $justificativo->estudiante->nombres }} {{ $justificativo->estudiante->apellidos }}
            </span>
        </td>
                            <td class="align-middle">{{ $justificativo->fecha_inicio->format('d/m/Y') }}</td>
                            <td class="align-middle">{{ $justificativo->fecha_fin->format('d/m/Y') }}</td>
                            <td class="align-middle">
                                <span class="badge badge-pill py-2 px-3 
                                    @if($justificativo->tipo == 'salud') badge-warning
                                    @elseif($justificativo->tipo == 'familiar') badge-info
                                    @else badge-secondary
                                    @endif">
                                    {{ ucfirst($justificativo->tipo) }}
                                </span>
                            </td>
                            <td class="align-middle">{{ Str::limit($justificativo->motivo, 50) }}</td>
                            <td class="text-center align-middle">
                                <span class="badge badge-pill py-2 px-3 
                                    @if($justificativo->aprobado) badge-success
                                    @else badge-warning
                                    @endif">
                                    {{ $justificativo->aprobado ? 'Aprobado' : 'Pendiente' }}
                                </span>
                            </td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('justificativos.show', $justificativo) }}" 
                                       class="btn btn-sm btn-light mx-1 border"
                                       data-toggle="tooltip" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye text-info"></i>
                                    </a>
                                    <a href="{{ route('justificativos.edit', $justificativo) }}" 
                                       class="btn btn-sm btn-light mx-1 border"
                                       data-toggle="tooltip" 
                                       title="Editar">
                                        <i class="fas fa-edit text-warning"></i>
                                    </a>
                                        <form action="{{ route('justificativos.destroy', $justificativo) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-light mx-1 border" 
                                                    data-toggle="tooltip" 
                                                    title="Eliminar"
                                                    data-nombre="{{ $justificativo->estudiante->nombres }} {{ $justificativo->estudiante->apellidos }}"
                                                    data-tipo="justificativo"
                                                    onclick="eliminarRegistro(this)">
                                                <i class="fas fa-trash text-danger"></i>
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
