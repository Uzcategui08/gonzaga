@extends('adminlte::page')

@section('title', 'Crear Horarios')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Crear Horarios</h1>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    @if(!empty($error))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ $error }}
        </div>
        @if(!empty($errorsList) && is_array($errorsList) && count($errorsList) > 1)
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errorsList as $msg)
                        <li>{{ $msg }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @elseif(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-plus-circle text-primary mr-2"></i>
                <span class="text-muted">Crear en bloque (una sola pantalla)</span>
            </h3>
        </div>

        <div class="card-body p-3">
            <form action="{{ route('horarios.store') }}" method="POST">
                @csrf

                @php
                    $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                    $scheduleOld = $schedule ?? old('schedule', []);
                    $profesorOld = $profesor_id ?? old('profesor_id', '');
                @endphp

                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    Crea varios bloques en cada día. Puedes agregar/quitar filas por día y guardar todo con un solo botón.
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-lg-6">
                                <div class="form-group mb-0">
                                    <label for="profesor_id" class="font-weight-bold text-gray-700">Profesor</label>
                                    <select name="profesor_id" id="profesor_id" class="form-control form-control-lg select2">
                                        <option value="">Todos</option>
                                        @foreach($professores as $profesor)
                                            <option value="{{ $profesor->id }}" {{ (string)$profesorOld === (string)$profesor->id ? 'selected' : '' }}>
                                                {{ $profesor->user->name ?? 'Sin nombre' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Filtra las materias/secciones disponibles en las cards según el profesor seleccionado.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    @foreach($dias as $dia)
                        @php
                            $entradas = (isset($scheduleOld[$dia]) && is_array($scheduleOld[$dia]) && count($scheduleOld[$dia]) > 0)
                                ? $scheduleOld[$dia]
                                : [ [] ];
                        @endphp

                        <div class="col-12 col-lg-6">
                            <div class="card card-outline card-primary">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="card-title mb-0 font-weight-bold">{{ $dia }}</h3>
                                    <button type="button" class="btn btn-sm btn-outline-primary js-add-row" data-day="{{ $dia }}">
                                        <i class="fas fa-plus mr-1"></i> Agregar
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped mb-0" data-day-table="{{ $dia }}">
                                            <thead>
                                                <tr>
                                                    <th style="min-width: 260px;">Materia / Sección</th>
                                                    <th style="min-width: 120px;">Inicio</th>
                                                    <th style="min-width: 120px;">Fin</th>
                                                    <th style="min-width: 140px;">Aula</th>
                                                    <th style="width: 1%;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($entradas as $i => $entrada)
                                                    <tr>
                                                        <td>
                                                            <select name="schedule[{{ $dia }}][{{ $i }}][asignacion_id]" class="form-control select2 js-asignacion-select">
                                                                <option value="">Seleccione una opción</option>
                                                                @foreach($asignaciones as $asignacion)
                                                                    @php
                                                                        $label = $asignacion->materia->nombre
                                                                            .' - '.$asignacion->gradoThroughSeccion->grado->nombre
                                                                            .' '.$asignacion->seccion->nombre
                                                                            .' ('.$asignacion->profesor->user->name.')';

                                                                        $selected = isset($entrada['asignacion_id']) && (string)$entrada['asignacion_id'] === (string)$asignacion->id;
                                                                    @endphp
                                                                    <option value="{{ $asignacion->id }}" data-profesor-id="{{ $asignacion->profesor_id }}" {{ $selected ? 'selected' : '' }}>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="time" name="schedule[{{ $dia }}][{{ $i }}][hora_inicio]" class="form-control"
                                                                   value="{{ $entrada['hora_inicio'] ?? '' }}">
                                                        </td>
                                                        <td>
                                                            <input type="time" name="schedule[{{ $dia }}][{{ $i }}][hora_fin]" class="form-control"
                                                                   value="{{ $entrada['hora_fin'] ?? '' }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="schedule[{{ $dia }}][{{ $i }}][aula]" class="form-control"
                                                                   placeholder="Ej: A101" value="{{ $entrada['aula'] ?? '' }}">
                                                        </td>
                                                        <td class="text-right">
                                                            <button type="button" class="btn btn-sm btn-outline-danger js-remove-row" title="Quitar fila">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-start mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save mr-1"></i> Guardar Horarios
                        </button>
                        <a href="{{ route('horarios.index') }}" class="btn btn-secondary btn-lg ml-2">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
        border-radius: 0.25rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: calc(2.25rem + 2px) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px) !important;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<template id="horario-row-template">
    <tr>
        <td>
            <select name="schedule[__DAY__][__INDEX__][asignacion_id]" class="form-control select2 js-asignacion-select">
                <option value="">Seleccione una opción</option>
                @foreach($asignaciones as $asignacion)
                    <option value="{{ $asignacion->id }}" data-profesor-id="{{ $asignacion->profesor_id }}">
                        {{ $asignacion->materia->nombre }} - {{ $asignacion->gradoThroughSeccion->grado->nombre }} {{ $asignacion->seccion->nombre }} ({{ $asignacion->profesor->user->name }})
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="time" name="schedule[__DAY__][__INDEX__][hora_inicio]" class="form-control">
        </td>
        <td>
            <input type="time" name="schedule[__DAY__][__INDEX__][hora_fin]" class="form-control">
        </td>
        <td>
            <input type="text" name="schedule[__DAY__][__INDEX__][aula]" class="form-control" placeholder="Ej: A101">
        </td>
        <td class="text-right">
            <button type="button" class="btn btn-sm btn-outline-danger js-remove-row" title="Quitar fila">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
    function getSelectedProfesorId() {
        const profesorSelect = document.getElementById('profesor_id');
        return profesorSelect ? (profesorSelect.value || '') : '';
    }

    function initSelect2Basic(root) {
        if (typeof $ === 'undefined' || !$.fn || !$.fn.select2) return;
        $(root).find('select.select2').not('.js-asignacion-select').select2({ width: '100%' });
    }

    function initAsignacionSelect2(root) {
        if (typeof $ === 'undefined' || !$.fn || !$.fn.select2) return;

        const matcher = function (params, data) {
            // data.id puede ser undefined en placeholders
            if (!data || !data.element) return data;
            const profesorId = getSelectedProfesorId();
            if (!profesorId) return data;

            const optProfesorId = data.element.getAttribute('data-profesor-id') || '';
            if (optProfesorId !== profesorId) {
                return null;
            }

            // Filtro por texto (comportamiento default)
            if (!params || !params.term) return data;
            const term = (params.term || '').toLowerCase();
            const text = (data.text || '').toLowerCase();
            return text.indexOf(term) > -1 ? data : null;
        };

        // Reinicializar solo los selects de asignación
        $(root).find('select.js-asignacion-select').each(function () {
            const $el = $(this);
            if ($el.data('select2')) {
                $el.select2('destroy');
            }
            $el.select2({ width: '100%', matcher });
        });
    }

    function ensureAsignacionSelectionsValid() {
        const profesorId = getSelectedProfesorId();
        if (!profesorId) return;

        document.querySelectorAll('select.js-asignacion-select').forEach((selectEl) => {
            const currentValue = selectEl.value;
            if (!currentValue) return;

            const selectedOpt = selectEl.selectedOptions && selectEl.selectedOptions[0] ? selectEl.selectedOptions[0] : null;
            const optProfesorId = selectedOpt ? (selectedOpt.getAttribute('data-profesor-id') || '') : '';
            if (optProfesorId !== profesorId) {
                if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
                    $(selectEl).val('').trigger('change');
                } else {
                    selectEl.value = '';
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initSelect2Basic(document);
        initAsignacionSelect2(document);
        ensureAsignacionSelectionsValid();

        const profesorSelect = document.getElementById('profesor_id');
        if (profesorSelect) {
            profesorSelect.addEventListener('change', function () {
                // Refiltra el dropdown (oculta opciones no permitidas)
                initAsignacionSelect2(document);
                ensureAsignacionSelectionsValid();
            });
        }

        const template = document.getElementById('horario-row-template');
        const templateHtml = template ? template.innerHTML.trim() : '';

        function addRow(day) {
            const table = document.querySelector('[data-day-table="' + day + '"]');
            if (!table) return;
            const tbody = table.querySelector('tbody');
            if (!tbody || !templateHtml) return;

            const index = tbody.querySelectorAll('tr').length;
            const rowHtml = templateHtml
                .replace(/__DAY__/g, day)
                .replace(/__INDEX__/g, String(index));

            tbody.insertAdjacentHTML('beforeend', rowHtml);
            initSelect2Basic(tbody);
            initAsignacionSelect2(tbody);
            ensureAsignacionSelectionsValid();
        }

        document.addEventListener('click', function (e) {
            const addBtn = e.target.closest('.js-add-row');
            if (addBtn) {
                addRow(addBtn.getAttribute('data-day'));
                return;
            }

            const removeBtn = e.target.closest('.js-remove-row');
            if (removeBtn) {
                const tr = removeBtn.closest('tr');
                if (tr) tr.remove();
            }
        });
    });
</script>
@stop
