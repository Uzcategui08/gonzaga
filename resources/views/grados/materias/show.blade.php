@extends('adminlte::page')

@section('title', 'Materias del Grado')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Materias de {{ $grado->nombre }} ({{$grado->nivel}})</h1>
    <div>
        <a href="{{ route('grados.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                Materias Asignadas
            </h3>
        </div>

        <div class="card-body p-3">
            @if($grado->materias->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No hay materias asignadas a este grado</p>
                </div>
            @else
                <div class="table-responsive-md rounded-lg" style="margin: 0.5rem;">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center py-3" style="width: 5%; min-width: 50px">#</th>
                                <th class="py-3" style="width: 95%">Nombre de la Materia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grado->materias as $materia)
                                <tr>
                                    <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                    <td class="align-middle">
                                        <span class="font-weight-semibold text-dark">{{ $materia->nombre }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div class="card-footer bg-white border-top-0">
            <div class="d-flex justify-content-start">
                <a href="{{ route('grado-materia.edit', $grado) }}" class="btn btn-warning">
                    <i class="fas fa-edit mr-1"></i> Editar Materias
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
