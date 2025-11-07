@extends('adminlte::page')

@section('title', 'Gestión de Limpiezas')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Gestión de Limpiezas</h1>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/datatables-responsive.css') }}">
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-body p-3">
            @if(session('success') || session('status') || session('error'))
                <div class="alert {{ session('success') ? 'alert-success' : (session('error') ? 'alert-danger' : 'alert-info') }}" role="alert">
                    {{ session('success') ?? session('error') ?? session('status') }}
                </div>
            @endif
            @if(isset($esAdmin) && $esAdmin)
                <div class="mb-4">
                    <h3 class="mb-3 text-muted">Asignados por Sección (Hoy)</h3>
                    <div class="table-responsive-md rounded-lg">
                        <table class="table table-hover mb-0 datatable" id="limpiezasSeccionTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-nowrap">#</th>
                                    <th class="py-3">Sección</th>
                                    <th class="py-3">Profesor</th>
                                    <th class="py-3">Horario</th>
                                    <th class="py-3">Estudiante</th>
                                    <th class="py-3">Tarea</th>
                                    <th class="text-center py-3">Realizada</th>
                                    <th class="text-center py-3">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $row = 1; @endphp
                                @foreach(($limpiezasSecciones ?? collect()) as $seccionId => $items)
                                    @foreach($items as $item)
                                        <tr>
                                            <td>{{ $row++ }}</td>
                                            <td>
                                                <span class="font-weight-semibold text-dark">{{ $item['seccion_nombre'] }}</span>
                                            </td>
                                            <td>{{ $item['profesor'] ?? 'Sin profesor' }}</td>
                                            <td>
                                                <span class="badge badge-pill py-2 px-3 badge-dark">{{ $item['hora'] }}</span>
                                            </td>
                                            <td>
                                                @php $lista = collect($item['estudiantes'] ?? []); @endphp
                                                @if($lista->isEmpty())
                                                    <em class="text-muted">Sin asignados</em>
                                                @else
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach($lista as $alumno)
                                                            <li>{{ $alumno['nombre'] }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                            <td>
                                                @if($lista->isEmpty())
                                                    <em class="text-muted">-</em>
                                                @else
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach($lista as $alumno)
                                                            <li>{{ $alumno['tarea'] }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($lista->isEmpty())
                                                    <span class="badge badge-secondary">N/A</span>
                                                @else
                                                    @php $pendientes = $lista->filter(fn($a)=>!($a['realizada']??false))->count(); @endphp
                                                    <span class="badge badge-pill py-2 px-3 {{ $pendientes===0 ? 'badge-success' : 'badge-warning' }}">
                                                        {{ $pendientes===0 ? 'Todas' : ($pendientes.' pend.') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @php $yaCreada = isset($limpiezasHoyPorHorario) && $limpiezasHoyPorHorario->has($item['horario_id']); @endphp
                                                @if($yaCreada)
                                                    <span class="badge badge-success">Ya creada</span>
                                                @else
                                                    <form method="POST" action="{{ route('limpiezas.materializar', $item['horario_id']) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary">
                                                            Crear limpieza
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($esCoordinador)
                <div class="mb-4">
                    <h3 class="mb-3 text-muted">Últimas clases del día</h3>
                    <div class="table-responsive-md rounded-lg">
                        <table class="table table-hover mb-0 datatable" id="clasesTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-nowrap py-3">#</th>
                                    <th class="py-3">Horario</th>
                                    <th class="py-3">Aula</th>
                                    <th class="py-3">Profesor</th>
                                    <th class="py-3">Materia</th>
                                    <th class="py-3">Sección</th>
                                    <th class="text-center py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clasesHoy as $clase)
                                    <tr class="align-middle">
                                        <td></td>
                                        <td>
                                            <span class="badge badge-pill py-2 px-3 badge-dark">
                                                {{ $clase->hora_inicio }} - {{ $clase->hora_fin }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-pill py-2 px-3 badge-secondary">
                                                {{ $clase->aula ?? 'Sin asignar' }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ optional(optional($clase->asignacion->profesor)->usuario)->name ?? 'Sin profesor' }}
                                        </td>
                                        <td class="text-truncate" style="max-width: 200px;" title="{{ $clase->asignacion->materia->nombre ?? 'Sin materia' }}">
                                            {{ $clase->asignacion->materia->nombre ?? 'Sin materia' }}
                                        </td>
                                        <td>
                                            {{ $clase->asignacion->seccion->nombre ?? 'Sin sección' }}
                                            @if($clase->asignacion->seccion && $clase->asignacion->seccion->grado)
                                                <small class="text-muted">({{ $clase->asignacion->seccion->grado->nombre }})</small>
                                            @endif
                                        </td>
                                        <td class="text-center py-2">
                                            <div class="d-flex justify-content-center">
                                                <a href="{{ route('limpiezas.create', $clase->id) }}" 
                                                   class="btn btn-sm btn-light mx-1 border"
                                                   data-toggle="tooltip" 
                                                   title="Asignar limpieza">
                                                    <i class="fas fa-broom text-primary"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                                <p class="mb-0">No hay clases programadas para hoy</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div>
                <h3 class="mb-3 text-muted">Limpiezas Registradas</h3>
                <div class="table-responsive-md rounded-lg">
                    <table class="table table-hover mb-0 datatable" id="limpiezasTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-nowrap">#</th>
                                <th class="py-3">Fecha</th>
                                <th class="py-3">Horario</th>
                                <th class="py-3">Aula</th>
                                <th class="py-3">Profesor</th>
                                <th class="py-3">Estado</th>
                                <th class="text-center py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($limpiezas as $limpieza)
                                <tr class="align-middle {{ $limpieza->realizada ? 'table-success' : '' }}">
                                    <td class="text-nowrap align-middle">{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $limpieza->fecha->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        <span class="badge badge-pill py-2 px-3 badge-dark">
                                            {{ $limpieza->hora_inicio->format('H:i') }} - {{ $limpieza->hora_fin->format('H:i') }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $aula = $limpieza->horario ? $limpieza->horario->aula : null;
                                        @endphp
                                        <span class="badge badge-pill py-2 px-3 
                                            {{ $aula ? 'badge-secondary' : 'badge-warning' }}">
                                            {{ $aula ?? 'Sin asignar' }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $limpieza->profesor->usuario->name ?? 'Sin asignar' }}
                                    </td>
                                    <td>
                                        <span class="badge badge-pill py-2 px-3 
                                            {{ $limpieza->realizada ? 'badge-success' : 'badge-warning' }}">
                                            {{ $limpieza->realizada ? 'Realizada' : 'Pendiente' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            @if($esCoordinador)
                                                <a href="{{ route('limpiezas.show', $limpieza) }}" 
                                                   class="btn btn-sm btn-light mx-1 border"
                                                   data-toggle="tooltip" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                                <form action="{{ route('limpiezas.destroy', $limpieza) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-light mx-1 border"
                                                            data-toggle="tooltip" 
                                                            title="Eliminar"
                                                            onclick="event.preventDefault(); eliminarRegistro(this)"
                                                            data-nombre="{{ $limpieza->profesor->usuario->name ?? 'Limpieza sin asignar' }}"
                                                            data-tipo="la limpieza">
                                                        <i class="fas fa-trash-alt text-danger"></i>
                                                    </button>
                                                </form>
                                            @else
                                                @if(!$limpieza->realizada)
                                                    <a href="{{ route('limpiezas.edit', $limpieza) }}" 
                                                        class="btn btn-sm btn-light mx-1 border"
                                                        data-toggle="tooltip" 
                                                        title="Editar">
                                                        <i class="fas fa-edit text-warning"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('limpiezas.show', $limpieza) }}" 
                                                   class="btn btn-sm btn-light mx-1 border"
                                                   data-toggle="tooltip" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-broom fa-3x mb-3"></i>
                                            <p class="mb-0">No hay limpiezas registradas</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
    .card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .card-header {
        border-bottom: none;
        padding: 1rem 1.25rem;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e3e6f0;
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0,0,0,0.02);
    }
    
    .bg-light-success {
        background-color: rgba(40, 167, 69, 0.1) !important;
    }
    
    /* Badges */
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        letter-spacing: 0.5px;
    }
    
    .bg-gray {
        background-color: #f8f9fc;
        color: #5a5c69;
    }
    
    .bg-indigo {
        background-color: #6610f2;
    }
    
    .btn-rounded-circle {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
</style>
@endpush
