@extends('adminlte::page')

@section('title', 'Editar Asignación')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Editar Asignación</h1>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-edit text-warning mr-2"></i>
            </h3>
        </div>

        <div class="card-body p-3">
            <form id="asignacionForm" action="{{ route('asignaciones.update', $asignacion->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="profesor_id" class="font-weight-bold text-gray-700">Profesor</label>
                        <select name="profesor_id" id="profesor_id" class="form-control form-control-lg select2 @error('profesor_id') is-invalid @enderror" required>
                            <option value="">Seleccione un profesor</option>
                            @foreach($profesores as $profesor)
                                <option value="{{ $profesor->id }}" {{ $asignacion->profesor_id == $profesor->id ? 'selected' : '' }}>
                                    {{ $profesor->user->name }} ({{ $profesor->codigo_profesor }})
                                </option>
                            @endforeach
                        </select>
                        @error('profesor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="materia_id" class="font-weight-bold text-gray-700">Materia</label>
                        <select name="materia_id" id="materia_id" class="form-control form-control-lg select2 @error('materia_id') is-invalid @enderror" required>
                            <option value="">Seleccione una materia</option>
                            @foreach($materias as $materia)
                                <option value="{{ $materia->id }}" data-nivel="{{ $materia->nivel }}" {{ $asignacion->materia_id == $materia->id ? 'selected' : '' }}>
                                    {{ $materia->nombre }} - {{ ucfirst($materia->nivel) }}
                                </option>
                            @endforeach
                        </select>
                        @error('materia_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="seccion_id" class="font-weight-bold text-gray-700">Sección</label>
                        <select name="seccion_id" id="seccion_id" class="form-control form-control-lg select2 @error('seccion_id') is-invalid @enderror" required>
                            <option value="">Seleccione una sección</option>
                            @foreach($secciones as $seccion)
                                <option value="{{ $seccion->id }}" data-nivel="{{ $seccion->grado->nivel ?? '' }}" {{ $asignacion->seccion_id == $seccion->id ? 'selected' : '' }}>
                                    {{ $seccion->nombre }} - {{ $seccion->grado->nombre }} ({{ ucfirst($seccion->grado->nivel ?? 'N/A') }})
                                </option>
                            @endforeach
                        </select>
                        @error('seccion_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Tabla de estudiantes -->
                <div class="mt-4">
                    <h5 class="font-weight-bold text-gray-700 mb-3">Estudiantes de la Sección</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="estudiantes-table">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center">
                                        <div class="d-flex flex-column align-items-center">
                                            <div>
                                                <input type="checkbox" id="select-all-estudiantes" title="Seleccionar todos">
                                            </div>
                                            <div class="mt-1 small text-muted">
                                                <input type="checkbox" id="select-top-half" title="Seleccionar mitad superior"> <span class="ml-1">Top</span>
                                                <input type="checkbox" id="select-bottom-half" title="Seleccionar mitad inferior" class="ml-2"> <span class="ml-1">Bottom</span>
                                                <input type="checkbox" id="select-male" title="Seleccionar hombres" class="ml-2"> <span class="ml-1">H</span>
                                                <input type="checkbox" id="select-female" title="Seleccionar mujeres" class="ml-2"> <span class="ml-1">M</span>
                                            </div>
                                        </div>
                                    </th>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Cédula</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los estudiantes se cargarán aquí dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="d-flex flex-wrap justify-content-start">
                        <button type="submit" class="btn btn-warning btn-lg mr-2 mb-2">
                            <i class="fas fa-save mr-1"></i> Actualizar
                        </button>
                        <a href="{{ route('asignaciones.index') }}" class="btn btn-secondary btn-lg ml-2 mb-2">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
// Función para manejar el envío del formulario
document.getElementById('asignacionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // Validar que al menos un estudiante esté seleccionado
    const checkboxes = document.querySelectorAll('.estudiante-checkbox:checked');
    if (checkboxes.length === 0) {
        Swal.fire({
            title: 'Error',
            text: 'Debe seleccionar al menos un estudiante',
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
        return false;
    }
    // Si hay estudiantes seleccionados, enviar el formulario
    this.submit();
});

$(document).ready(function() {
        // Filtrar secciones por nivel de la materia seleccionada
        const $materia = $('#materia_id');
        const $seccion = $('#seccion_id');

        function loadSeccionesByNivel(nivel, keepSelection=false) {
            const selectedVal = keepSelection ? $seccion.val() : '';
            $seccion.empty();
            $seccion.append(new Option('Seleccione una sección', ''));
            if (!nivel) {
                $seccion.trigger('change.select2');
                return;
            }
            $.getJSON('{{ route('asignaciones.por.nivel') }}', { nivel }, function(resp){
                if (resp.success) {
                    resp.secciones.forEach(function(s){
                        const text = `${s.nombre} - ${s.grado} (${(s.nivel||'').charAt(0).toUpperCase()+ (s.nivel||'').slice(1)})`;
                        const opt = new Option(text, s.id, false, keepSelection && s.id.toString() === (selectedVal||'').toString());
                        $seccion.append(opt);
                    });
                    $seccion.trigger('change.select2');
                }
            });
        }

        $materia.on('change', function() {
            const nivel = ($(this).find('option:selected').data('nivel') || '').toLowerCase();
            loadSeccionesByNivel(nivel);
            $('#estudiantes-table tbody').html('<tr><td colspan="5" class="text-center">Seleccione una sección para ver los estudiantes</td></tr>');
        });

        // Aplicar carga inicial según materia seleccionada, manteniendo la sección si coincide
        const nivelInicial = ($materia.find('option:selected').data('nivel') || '').toLowerCase();
        if (nivelInicial) {
            loadSeccionesByNivel(nivelInicial, true);
        }
    // Inicializar select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Cargar estudiantes de la sección seleccionada
    function cargarEstudiantes(seccionId, seleccionados = []) {
        var tbody = $('#estudiantes-table tbody');
        if (seccionId) {
            tbody.html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando estudiantes...</td></tr>');
            $.ajax({
                url: '{{ route("asignaciones.estudiantes.por-seccion") }}',
                type: 'GET',
                data: { seccion_id: seccionId },
                success: function(response) {
                    if (response.success && response.estudiantes && response.estudiantes.length > 0) {
                        var html = '';
                        
                        response.estudiantes.forEach(function(estudiante) {
                            // comparar como strings para evitar problemas de tipo
                            var estudianteIdStr = estudiante.id.toString();
                            var checked = seleccionados.includes(estudianteIdStr) ? 'checked' : '';
                            var genero = (estudiante.genero || '').toString().toUpperCase();
                            html += '<tr>';
                            html += '<td class="text-center"><input type="checkbox" name="estudiantes_id[]" value="' + estudiante.id + '" class="estudiante-checkbox" data-genero="' + genero + '" ' + checked + '></td>';
                            html += '<td>' + estudiante.id + '</td>';
                            html += '<td>' + estudiante.nombre_completo + '</td>';
                            html += '<td>' + (estudiante.cedula || 'N/A') + '</td>';
                            html += '<td><span class="badge ' + (estudiante.estado === 'activo' ? 'badge-success' : 'badge-secondary') + '">' + (estudiante.estado || 'N/A') + '</span></td>';
                            html += '</tr>';
                        });
                        tbody.html(html);
                        // reset toggles al recargar tabla
                        $('#select-top-half, #select-bottom-half, #select-male, #select-female').prop('checked', false);
                        updateSelectAllState();
                    } else {
                        tbody.html('<tr><td colspan="5" class="text-center">No hay estudiantes en esta sección</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar estudiantes:', error);
                    tbody.html('<tr><td colspan="5" class="text-center text-danger">Error al cargar los estudiantes</td></tr>');
                }
            });
        } else {
            tbody.html('<tr><td colspan="5" class="text-center">Seleccione una sección para ver los estudiantes</td></tr>');
        }
    }

    // Al cambiar la sección, cargar estudiantes
    $('#seccion_id').change(function() {
        var seccionId = $(this).val();
        cargarEstudiantes(seccionId);
    });

    // Cargar estudiantes al cargar la página si hay sección seleccionada
    var seccionInicial = $('#seccion_id').val();
    // Obtener los IDs guardados en la asignación (campo JSON estudiantes_id)
    var estudiantesSeleccionados = @json($asignacion->estudiantes_id ? json_decode($asignacion->estudiantes_id, true) : []);
    // Normalizar a strings para comparar con los ids que vienen del servidor (evita fallo por tipo)
    estudiantesSeleccionados = estudiantesSeleccionados.map(function(v) { return v.toString(); });
    if (seccionInicial) {
        cargarEstudiantes(seccionInicial, estudiantesSeleccionados);
    }

    // Función para actualizar el estado del checkbox "select all"
    function updateSelectAllState() {
        var all = $('.estudiante-checkbox');
        if (all.length === 0) {
            $('#select-all-estudiantes').prop('checked', false).prop('indeterminate', false);
            return;
        }
        var checkedCount = all.filter(':checked').length;
        $('#select-all-estudiantes').prop('checked', checkedCount === all.length);
        $('#select-all-estudiantes').prop('indeterminate', checkedCount > 0 && checkedCount < all.length);
    }

    // Delegated event: cuando cualquier checkbox individual cambie, actualizar encabezado
    $(document).on('change', '.estudiante-checkbox', function() {
        updateSelectAllState();
    });

    // Manejar el toggle de "select all"
    $('#select-all-estudiantes').on('change', function() {
        var checked = $(this).is(':checked');
        $('.estudiante-checkbox').prop('checked', checked);
        $('#select-top-half, #select-bottom-half, #select-male, #select-female').prop('checked', false);
    });

    function toggleGeneroSelection(genero, checked) {
        $('.estudiante-checkbox').each(function() {
            var g = (($(this).data('genero') || '') + '').toUpperCase();
            if (g === genero) {
                $(this).prop('checked', checked);
            }
        });
        // reset mitades cuando se usa selección por género
        $('#select-top-half, #select-bottom-half').prop('checked', false);
        updateSelectAllState();
    }

    $('#select-male').on('change', function() {
        toggleGeneroSelection('M', $(this).is(':checked'));
    });

    $('#select-female').on('change', function() {
        toggleGeneroSelection('F', $(this).is(':checked'));
    });

    // Seleccionar mitad superior
    $('#select-top-half').on('change', function() {
        var checked = $(this).is(':checked');
        var all = $('.estudiante-checkbox');
        var half = Math.ceil(all.length / 2);
        all.prop('checked', false);
        all.slice(0, half).prop('checked', checked);
        // reset other half checkbox
        $('#select-bottom-half').prop('checked', false);
        // reset género
        $('#select-male, #select-female').prop('checked', false);
        updateSelectAllState();
    });

    // Seleccionar mitad inferior
    $('#select-bottom-half').on('change', function() {
        var checked = $(this).is(':checked');
        var all = $('.estudiante-checkbox');
        var half = Math.ceil(all.length / 2);
        all.prop('checked', false);
        all.slice(half).prop('checked', checked);
        // reset top half checkbox
        $('#select-top-half').prop('checked', false);
        // reset género
        $('#select-male, #select-female').prop('checked', false);
        updateSelectAllState();
    });
});
</script>
@endpush

@stop