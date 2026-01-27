@extends('adminlte::page')

@section('title', 'Retiros')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Retiros</h1>
    <a href="{{ route('estudiantes.index') }}" class="btn btn-light border">
        <i class="fas fa-arrow-left mr-1"></i> Volver a estudiantes
    </a>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-user-slash text-danger mr-2"></i>Retirar estudiante
                </h3>
                <span class="text-muted small mt-2 mt-md-0">
                    Esto marca al estudiante como <b>inactivo</b> y lo quita de las asignaciones; no borra asistencias históricas.
                </span>
            </div>
        </div>

        <div class="card-body p-3">
            @if(isset($estudiantes) && $estudiantes->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-user-check fa-3x mb-3"></i>
                    <p class="mb-0">No hay estudiantes activos para retirar.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped datatable">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">#</th>
                                <th>Código</th>
                                <th>Nombre Completo</th>
                                <th>Grado</th>
                                <th>Sección</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estudiantes as $estudiante)
                                <tr>
                                    <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                    <td class="align-middle">{{ $estudiante->codigo_estudiante }}</td>
                                    <td class="align-middle">{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</td>
                                    <td class="align-middle">{{ $estudiante->seccion?->grado?->nombre }}</td>
                                    <td class="align-middle">{{ $estudiante->seccion?->nombre }}</td>
                                    <td class="text-center align-middle">
                                        <form method="POST" action="{{ route('estudiantes.retirar', ['estudiante' => $estudiante->id]) }}" class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                onclick="event.preventDefault(); confirmarRetiro(this)"
                                                data-nombre="{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}">
                                                <i class="fas fa-user-slash mr-1"></i> Retirar
                                            </button>
                                        </form>
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
@stop

@section('js')
<script>
    function confirmarRetiro(button) {
        const form = $(button).closest('form');
        const nombre = $(button).data('nombre') || 'el estudiante';

        Swal.fire({
            title: 'Confirmar retiro',
            text: `¿Seguro que deseas retirar a ${nombre}? Se marcará como inactivo y se quitará de asignaciones.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, retirar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
</script>
@stop
