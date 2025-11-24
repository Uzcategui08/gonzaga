@extends('adminlte::page')

@section('title', 'Nueva Asignación')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 text-dark">Nueva Asignación</h1>
</div>
@stop

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-plus-circle text-primary mr-2"></i>
            </h3>
        </div>

        <div class="card-body p-3">
            <form id="asignacionForm" action="{{ route('asignaciones.store') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="profesor_id" class="font-weight-bold text-gray-700">Profesor</label>
                        <select name="profesor_id" id="profesor_id" class="form-control form-control-lg select2 @error('profesor_id') is-invalid @enderror" required>
                            <option value="">Seleccione un profesor</option>
                            @foreach($profesores as $profesor)
                                <option value="{{ $profesor->id }}">
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
                                <option value="{{ $materia->id }}" data-nivel="{{ $materia->nivel }}">
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
                                <option value="{{ $seccion->id }}" data-nivel="{{ $seccion->grado->nivel ?? '' }}">
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
                        <button type="submit" class="btn btn-primary btn-lg mr-2 mb-2">
                        <i class="fas fa-save mr-1"></i> Guardar 
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
    // Inicializar select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    $('#seccion_id').change(function() {
        var seccionId = $(this).val();
        var tbody = $('#estudiantes-table tbody');
        
        if (seccionId) {
            // Mostrar carga
            tbody.html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando estudiantes...</td></tr>');
            
            // Hacer la petición AJAX
            $.ajax({
                url: '{{ route("asignaciones.estudiantes.por-seccion") }}',
                type: 'GET',
                data: {
                    seccion_id: seccionId
                },
                success: function(response) {
                    if (response.success && response.estudiantes && response.estudiantes.length > 0) {
                        var html = '';
                        response.estudiantes.forEach(function(estudiante) {
                            html += '<tr>';
                            html += '<td class="text-center"><input type="checkbox" name="estudiantes_id[]" value="' + estudiante.id + '" class="estudiante-checkbox"></td>';
                            html += '<td>' + estudiante.id + '</td>';
                            html += '<td>' + estudiante.nombre_completo + '</td>';
                            html += '<td>' + (estudiante.cedula || 'N/A') + '</td>';
                            html += '<td><span class="badge ' + (estudiante.estado === 'activo' ? 'badge-success' : 'badge-secondary') + '">' + (estudiante.estado || 'N/A') + '</span></td>';
                            html += '</tr>';
                        });
                        tbody.html(html);
                        // Actualizar estado del checkbox "select all" después de renderizar
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
            tbody.html('<tr><td colspan="4" class="text-center">Seleccione una sección para ver los estudiantes</td></tr>');
        }
    });

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
        updateSelectAllState();
    });
});
</script>
@endpush

@stop
