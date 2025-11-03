@extends('adminlte::page')

@section('title', 'Lista Personalizada de Estudiantes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Lista personalizada</h1>
        <a href="{{ route('asistencias.coordinador.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Volver al resumen
        </a>
    </div>
    <hr class="mt-2 mb-4">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-3">Configura la lista</h4>
                    <form method="POST" action="{{ route('asistencias.coordinador.custom-list.pdf') }}" target="_blank">
                        @csrf
                        @php
                            $defaults = $defaults ?? [];
                            $nameWidthValue = old('name_column_width', $old['name_column_width'] ?? ($defaults['name_column_width'] ?? ''));
                            $extraWidthValue = old('extra_field_width', $old['extra_field_width'] ?? ($defaults['extra_field_width'] ?? ''));
                            $customWidthValue = old('custom_column_width', $old['custom_column_width'] ?? ($defaults['custom_column_width'] ?? ''));
                            $rowHeightValue = old('row_height', $old['row_height'] ?? ($defaults['row_height'] ?? ''));
                        @endphp
                        <div class="form-group">
                            <label for="seccion_id" class="font-weight-semibold">Sección</label>
                            <select name="seccion_id" id="seccion_id" class="form-control @error('seccion_id') is-invalid @enderror" required>
                                <option value="">Selecciona una sección</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" {{ (int) old('seccion_id', $old['seccion_id']) === $section->id ? 'selected' : '' }}>
                                        {{ optional($section->grado)->nombre ?? 'Sin grado' }} - {{ $section->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('seccion_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="extra_field" class="font-weight-semibold">Columna adicional (datos de la BDD)</label>
                            <select name="extra_field" id="extra_field" class="form-control @error('extra_field') is-invalid @enderror">
                                <option value="">Ninguna</option>
                                @foreach($availableFields as $fieldKey => $fieldLabel)
                                    <option value="{{ $fieldKey }}" {{ old('extra_field', $old['extra_field']) === $fieldKey ? 'selected' : '' }}>
                                        {{ $fieldLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('extra_field')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Esta columna se llenará automáticamente para cada estudiante.</small>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-semibold d-block">Columnas personalizadas</label>
                            <small class="text-muted d-block mb-2">Escribe los títulos de columnas adicionales. Las celdas aparecerán en blanco en el PDF.</small>
                            <div id="custom-columns-wrapper">
                                @php
                                    $customColumnsOld = old('custom_columns', $old['custom_columns']);
                                @endphp
                                @if(!empty($customColumnsOld))
                                    @foreach($customColumnsOld as $index => $title)
                                        <div class="input-group mb-2 custom-column-row">
                                            <input type="text" name="custom_columns[]" class="form-control" value="{{ $title }}" placeholder="Título de la columna">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-danger remove-column" type="button" title="Eliminar columna">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-column-btn">
                                <i class="fas fa-plus mr-1"></i>Agregar columna
                            </button>
                            @error('custom_columns')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            @error('custom_columns.*')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="border rounded p-3 mb-3">
                            <h5 class="font-weight-semibold mb-3">Diseño del PDF</h5>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="name_column_width" class="font-weight-semibold">Ancho columna "Apellidos y Nombres" (mm)</label>
                                    <input type="number" step="0.1" min="10" max="200" name="name_column_width" id="name_column_width" class="form-control @error('name_column_width') is-invalid @enderror" value="{{ $nameWidthValue }}" placeholder="Ej. 60">
                                    @error('name_column_width')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="extra_field_width" class="font-weight-semibold">Ancho columna adicional (mm)</label>
                                    <input type="number" step="0.1" min="10" max="200" name="extra_field_width" id="extra_field_width" class="form-control @error('extra_field_width') is-invalid @enderror" value="{{ $extraWidthValue }}" placeholder="Ej. 30">
                                    @error('extra_field_width')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Se aplica solo si seleccionas una columna de la BDD.</small>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="custom_column_width" class="font-weight-semibold">Ancho de columnas personalizadas (mm)</label>
                                    <input type="number" step="0.1" min="10" max="200" name="custom_column_width" id="custom_column_width" class="form-control @error('custom_column_width') is-invalid @enderror" value="{{ $customWidthValue }}" placeholder="Ej. 30">
                                    @error('custom_column_width')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="row_height" class="font-weight-semibold">Alto de filas (mm)</label>
                                    <input type="number" step="0.1" min="5" max="80" name="row_height" id="row_height" class="form-control @error('row_height') is-invalid @enderror" value="{{ $rowHeightValue }}" placeholder="Ej. 12">
                                    @error('row_height')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <small class="text-muted">Los valores son opcionales. Dejar en blanco utilizará los tamaños sugeridos.</small>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-pdf mr-2"></i>Generar PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('custom-columns-wrapper');
        const addColumnBtn = document.getElementById('add-column-btn');

        function createColumnRow(value = '') {
            const row = document.createElement('div');
            row.className = 'input-group mb-2 custom-column-row';

            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'custom_columns[]';
            input.className = 'form-control';
            input.placeholder = 'Título de la columna';
            input.value = value;

            const append = document.createElement('div');
            append.className = 'input-group-append';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-outline-danger remove-column';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.title = 'Eliminar columna';
            removeBtn.addEventListener('click', function () {
                wrapper.removeChild(row);
            });

            append.appendChild(removeBtn);
            row.appendChild(input);
            row.appendChild(append);

            return row;
        }

        addColumnBtn.addEventListener('click', function () {
            wrapper.appendChild(createColumnRow());
        });

        if (wrapper.children.length === 0) {
            wrapper.appendChild(createColumnRow());
        }
    });
</script>
@endsection
