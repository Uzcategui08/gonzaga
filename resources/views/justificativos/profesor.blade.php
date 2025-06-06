@extends('adminlte::page')

@section('title', 'Justificativos de Estudiantes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Justificativos de Estudiantes</h1>
    </div>
@endsection

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
                <table class="table table-hover mb-0 datatable" id="justificativos-profesor">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center py-3" style="width: 5%; min-width: 50px">#</th>
                            <th class="py-3" style="width: 25%; min-width: 200px">Estudiante</th>
                            <th class="py-3" style="width: 15%; min-width: 120px">Fecha Inicio</th>
                            <th class="py-3" style="width: 15%; min-width: 120px">Fecha Fin</th>
                            <th class="py-3" style="width: 15%; min-width: 100px">Tipo</th>
                            <th class="py-3" style="width: 25%; min-width: 200px">Motivo</th>
                            <th class="text-center py-3" style="width: 10%; min-width: 120px">Estado</th>
                            <th class="text-center py-3" style="width: 10%; min-width: 120px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($justificativos as $justificativo)
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
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
                                    <a href="{{ route('justificativos.profesor.show', ['justificativo' => $justificativo->id]) }}" 
                                       class="btn btn-sm btn-light mx-1 border"
                                       data-toggle="tooltip" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye text-info"></i>
                                    </a>
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

