@extends('adminlte::page')

@section('title', 'Horario Primaria - ' . $seccion->nombre)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Horario Primaria - {{ $seccion->grado->nombre }} {{ $seccion->nombre }}</h1>
    <div>
        <a href="{{ route('secciones.index') }}" class="btn btn-secondary btn-sm">
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
                <i class="fas fa-calendar-alt text-primary mr-2"></i> Editor por sección
            </h3>
            <div class="small text-muted">
                Titular: {{ $seccion->titular?->user?->name ?? 'Sin titular' }}
            </div>
        </div>
        <form action="{{ route('horarios.primaria.update', $seccion) }}" method="POST">
            @csrf
            <div class="card-body">
                @foreach($dias as $dia)
                    <div class="mb-4 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0">{{ $dia }}</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow('{{ $dia }}')">
                                <i class="fas fa-plus mr-1"></i> Agregar bloque
                            </button>
                        </div>
                        <table class="table table-sm mb-0" id="tabla-{{ $dia }}">
                            <thead>
                                <tr>
                                    <th style="width: 140px">Inicio</th>
                                    <th style="width: 140px">Fin</th>
                                    <th>Materia</th>
                                    <th>Aula</th>
                                    <th>Profesor (opcional)</th>
                                    <th style="width: 40px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($porDia[$dia] ?? []) as $h)
                                    <tr>
                                        <td>
                                            <input type="time" name="schedule[{{ $dia }}][][hora_inicio]" value="{{ $h->hora_inicio }}" class="form-control form-control-sm" required>
                                        </td>
                                        <td>
                                            <input type="time" name="schedule[{{ $dia }}][][hora_fin]" value="{{ $h->hora_fin }}" class="form-control form-control-sm" required>
                                        </td>
                                        <td>
                                            <select name="schedule[{{ $dia }}][][materia_id]" class="form-control form-control-sm" required>
                                                <option value="">Seleccione</option>
                                                @foreach($materias as $m)
                                                    <option value="{{ $m->id }}" {{ $h->asignacion->materia->id === $m->id ? 'selected' : '' }}>{{ $m->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="schedule[{{ $dia }}][][aula]" value="{{ $h->aula }}" class="form-control form-control-sm" placeholder="Aula">
                                        </td>
                                        <td>
                                            <select name="schedule[{{ $dia }}][][profesor_id]" class="form-control form-control-sm">
                                                <option value="">Usar titular ({{ $seccion->titular?->user?->name ?? 'N/A' }})</option>
                                                @foreach($profesores as $p)
                                                    <option value="{{ $p->id }}" {{ ($h->asignacion->profesor->id === $p->id) ? 'selected' : '' }}>{{ $p->user->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-light" onclick="this.closest('tr').remove()" title="Eliminar fila">
                                                <i class="fas fa-trash text-danger"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
            <div class="card-footer bg-white d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Guardar horario
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
function addRow(dia) {
    const tbody = document.querySelector(`#tabla-${CSS.escape(dia)} tbody`);
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="time" name="schedule[${dia}][][hora_inicio]" class="form-control form-control-sm" required></td>
        <td><input type="time" name="schedule[${dia}][][hora_fin]" class="form-control form-control-sm" required></td>
        <td>
            <select name="schedule[${dia}][][materia_id]" class="form-control form-control-sm" required>
                <option value="">Seleccione</option>
                @foreach($materias as $m)
                    <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" name="schedule[${dia}][][aula]" class="form-control form-control-sm" placeholder="Aula"></td>
        <td>
            <select name="schedule[${dia}][][profesor_id]" class="form-control form-control-sm">
                <option value="">Usar titular ({{ $seccion->titular?->user?->name ?? 'N/A' }})</option>
                @foreach($profesores as $p)
                    <option value="{{ $p->id }}">{{ $p->user->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-light" onclick="this.closest('tr').remove()" title="Eliminar fila">
                <i class="fas fa-trash text-danger"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}
</script>
@endsection
