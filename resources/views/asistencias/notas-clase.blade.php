@extends('adminlte::page')

@section('title', 'Notas por Clase')

@section('content_header')
    <h1>Notas por Clase</h1>
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
            @if(isset($asistencias) && $asistencias->isEmpty())
                <div class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-chalkboard-teacher fa-3x mb-3"></i>
                        <p class="mb-0">¡No hay notas de clase registradas!</p>
                        <p class="small text-muted mb-3">No se han registrado notas de clase aún.</p>
                    </div>
                </div>
            @elseif(isset($asistencias) && $asistencias->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0 datatable" id="notasClaseTable">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-nowrap">#</th>
                                <th>Día</th>
                                <th>Fecha</th>
                                <th>Materia</th>
                                <th>Año</th>
                                <th>Sección</th>
                                <th>Hora</th>
                                <th>Aula</th>
                                <th class="text-center align-middle">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asistencias as $asistencia)
                                <tr>
                                    <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                    <td class="align-middle">{{ ucfirst($asistencia->horario->dia) }}</td>
                                    <td class="align-middle">{{ \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y') }}</td>
                                    <td class="align-middle">{{ $asistencia->horario->asignacion->materia->nombre }}</td>
                                    <td class="align-middle">{{ $asistencia->horario->asignacion->seccion->grado->nombre }}</td>
                                    <td class="align-middle">{{ $asistencia->horario->asignacion->seccion->nombre }}</td>
                                    <td class="align-middle">{{ $asistencia->horario->hora_inicio }}</td>
                                    <td class="align-middle">{{ $asistencia->horario->aula }}</td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center">
                                            <a href="{{ route('asistencia.notas-clase.pdf-individual', $asistencia->id) }}" 
                                               target="_blank"
                                               class="btn btn-sm btn-light mx-1 border"
                                               data-toggle="tooltip" 
                                               title="Ver PDF">
                                                <i class="fas fa-file-pdf text-primary"></i>
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

