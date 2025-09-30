@extends('adminlte::page')

@section('title', 'Pases de Entrada')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Pases de Entrada</h1>
        <form method="GET" action="" class="form-inline mb-0">
            <div class="form-group mr-2">
                <label for="fecha" class="mb-0 mr-2">Fecha</label>
                <input type="date" name="fecha" id="fecha" class="form-control" value="{{ request('fecha', now()->format('Y-m-d')) }}">
            </div>
            <button type="submit" class="btn btn-primary mr-2">Filtrar</button>
        </form>
        <a href="{{ route('pases.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Pase
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-light">
            <h3 class="card-title">
                <i class="fas fa-list text-primary mr-2"></i>
                Listado
            </h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped datatable" id="pases-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width: 5%">#</th>
                            <th class="py-3">Grado - Sección</th>
                            <th style="width: 25%">Estudiante</th>
                            <th style="width: 15%">Fecha</th>
                            <th style="width: 10%">Hora</th>
                            <th style="width: 15%">Motivo</th>
                            <th class="text-center" style="width: 10%">Estado</th>
                            <th class="text-center" style="width: 10%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pases as $pase)
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                    <td class="align-middle">
            {{ $pase->estudiante->seccion->grado->nombre ?? '' }} - {{ $pase->estudiante->seccion->nombre ?? '' }}
        </td>
                            <td class="align-middle">
                                {{ $pase->estudiante->nombres }} {{ $pase->estudiante->apellidos }}
                            </td>
                            <td class="align-middle">{{ $pase->fecha->format('d/m/Y') }}</td>
                            <td class="align-middle">{{ $pase->hora_llegada->format('H:i') }}</td>
                            <td class="align-middle">{{ Str::limit($pase->motivo, 50) }}</td>
                            <td class="text-center align-middle">
                                <span class="badge badge-pill py-2 px-3 
                                    @if($pase->aprobado) badge-success
                                    @else badge-warning
                                    @endif">
                                    {{ $pase->aprobado ? 'Aprobado' : 'Pendiente' }}
                                </span>
                            </td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('pases.show', $pase) }}" 
                                       class="btn btn-sm btn-light mx-1 border"
                                       data-toggle="tooltip" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye text-info"></i>
                                    </a>
                                    <a href="{{ route('pases.edit', $pase) }}" 
                                       class="btn btn-sm btn-light mx-1 border"
                                       data-toggle="tooltip" 
                                       title="Editar">
                                        <i class="fas fa-edit text-warning"></i>
                                    </a>
                                    
                                    <form action="{{ route('pases.destroy', $pase) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-light mx-1 border" 
                                                data-toggle="tooltip" 
                                                title="Eliminar"
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
@stop