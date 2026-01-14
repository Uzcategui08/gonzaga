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
                        <label for="materias_id" class="font-weight-bold text-gray-700">Materias</label>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="aplicar_todas_materias" name="aplicar_todas_materias" value="1">
                            <label class="custom-control-label" for="aplicar_todas_materias">Seleccionar todas las materias</label>
                        </div>
                        <select name="materias_id[]" id="materias_id" class="form-control form-control-lg select2 @error('materias_id') is-invalid @enderror" multiple>
                            @foreach($materias as $materia)
                                <option value="{{ $materia->id }}" data-nivel="{{ $materia->nivel }}">
                                    {{ $materia->nombre }} - {{ ucfirst($materia->nivel) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="small text-muted mt-1">Puedes seleccionar varias materias</div>
                        @error('materias_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="secciones_id" class="font-weight-bold text-gray-700">Secciones</label>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="aplicar_todas_secciones" name="aplicar_todas_secciones" value="1">
                            <label class="custom-control-label" for="aplicar_todas_secciones">Aplicar a todas las secciones del profesor</label>
                        </div>
                        <select name="secciones_id[]" id="secciones_id" class="form-control form-control-lg select2 @error('secciones_id') is-invalid @enderror" multiple>
                            <option value="">Seleccione una o varias secciones</option>
                        </select>
                        <div class="small text-muted mt-1">Se cargan automáticamente según el profesor (y el nivel de la materia)</div>
                        @error('secciones_id')
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
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Seleccione un profesor y luego secciones (o marque “todas las secciones”) para ver los estudiantes</td>
                                </tr>
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
        const $profesor = $('#profesor_id');
        // Filtrar secciones por nivel de la materia seleccionada
    const $materias = $('#materias_id');
    const $todasMaterias = $('#aplicar_todas_materias');
        const $secciones = $('#secciones_id');
        const $todas = $('#aplicar_todas_secciones');

        let profesorSecciones = [];
        let profesorEstudiantes = [];

        function setInitialTableMessage(message) {
            $('#estudiantes-table tbody').html('<tr><td colspan="5" class="text-center text-muted">' + message + '</td></tr>');
        }

        function ucFirst(str) {
            if (!str) return '';
            str = str.toString();
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function nivelesMateriasSeleccionadas() {
            const niveles = [];
            $materias.find('option:selected').each(function() {
                const n = (($(this).data('nivel') || '') + '').toLowerCase();
                if (n) niveles.push(n);
            });
            return Array.from(new Set(niveles));
        }

        function getNivelParaFiltrarSecciones() {
            const niveles = nivelesMateriasSeleccionadas();
            // Si todas las materias seleccionadas son del mismo nivel, filtramos por ese.
            // Si hay mezcla de niveles o no hay selección, no filtramos.
            return niveles.length === 1 ? niveles[0] : '';
        }

        function seccionesFiltradasPorNivel() {
            const nivel = getNivelParaFiltrarSecciones();
            if (!nivel) return (profesorSecciones || []);
            return (profesorSecciones || []).filter(function(s) {
                return ((s.nivel || '') + '').toLowerCase() === nivel;
            });
        }

        function renderSeccionesOptions(keepSelection=false) {
            const filtered = seccionesFiltradasPorNivel();
            const prev = keepSelection ? ($secciones.val() || []).map(String) : [];

            $secciones.empty();
            filtered.forEach(function(s) {
                const text = `${s.nombre} - ${s.grado || ''} (${ucFirst(s.nivel || '')})`;
                const selected = keepSelection && prev.includes(String(s.id));
                $secciones.append(new Option(text, s.id, false, selected));
            });
            $secciones.trigger('change.select2');

            if ($todas.is(':checked')) {
                $secciones.val(filtered.map(s => String(s.id))).trigger('change.select2');
            }
        }

        function renderEstudiantesBySelectedSecciones() {
            if ($todas.is(':checked')) {
                renderEstudiantes(profesorEstudiantes || []);
                return;
            }

            const selected = ($secciones.val() || []).map(String);
            if (!selected.length) {
                setInitialTableMessage('Seleccione una o varias secciones para ver los estudiantes');
                return;
            }

            const filtered = (profesorEstudiantes || []).filter(function(e) {
                return selected.includes(String(e.seccion_id));
            });
            renderEstudiantes(filtered);
        }

        function syncModoTodasSecciones() {
            var enabled = $todas.is(':checked');
            $secciones.prop('disabled', enabled);
            if (enabled) {
                const filtered = seccionesFiltradasPorNivel();
                $secciones.val(filtered.map(s => String(s.id))).trigger('change.select2');
            }
        }

        function syncModoTodasMaterias() {
            const enabled = $todasMaterias.is(':checked');
            $materias.prop('disabled', enabled);
            if (enabled) {
                const allIds = [];
                $materias.find('option').each(function() {
                    const v = $(this).attr('value');
                    if (v) allIds.push(String(v));
                });
                $materias.val(allIds).trigger('change.select2');
            }
        }

        $materias.on('change', function() {
            renderSeccionesOptions(true);
            renderEstudiantesBySelectedSecciones();
        });

        $todasMaterias.on('change', function() {
            syncModoTodasMaterias();
            renderSeccionesOptions(true);
            syncModoTodasSecciones();
            renderEstudiantesBySelectedSecciones();
        });
    // Inicializar select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    function renderEstudiantes(estudiantes) {
        var tbody = $('#estudiantes-table tbody');
        if (estudiantes && estudiantes.length > 0) {
            var html = '';
            estudiantes.forEach(function(estudiante) {
                var genero = (estudiante.genero || '').toString().toUpperCase();
                var seccionId = (estudiante.seccion_id || '').toString();
                var nombre = estudiante.nombre_completo;
                if (estudiante.seccion_nombre) {
                    nombre = nombre + ' - ' + estudiante.seccion_nombre;
                }
                html += '<tr>';
                html += '<td class="text-center"><input type="checkbox" name="estudiantes_id[]" value="' + estudiante.id + '" class="estudiante-checkbox" data-genero="' + genero + '" data-seccion-id="' + seccionId + '"></td>';
                html += '<td>' + estudiante.id + '</td>';
                html += '<td>' + nombre + '</td>';
                html += '<td>' + (estudiante.cedula || 'N/A') + '</td>';
                html += '<td><span class="badge ' + (estudiante.estado === 'activo' ? 'badge-success' : 'badge-secondary') + '">' + (estudiante.estado || 'N/A') + '</span></td>';
                html += '</tr>';
            });
            tbody.html(html);
            $('#select-top-half, #select-bottom-half, #select-male, #select-female').prop('checked', false);
            updateSelectAllState();
        } else {
            setInitialTableMessage('No hay estudiantes para mostrar');
        }
    }

    function cargarEstudiantesPorProfesor(profesorId) {
        var tbody = $('#estudiantes-table tbody');
        if (!profesorId) {
            profesorSecciones = [];
            profesorEstudiantes = [];
            $secciones.empty().trigger('change.select2');
            $secciones.prop('disabled', true);
            setInitialTableMessage('Seleccione un profesor');
            return;
        }
        tbody.html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando estudiantes...</td></tr>');
        $.ajax({
            url: '{{ route("asignaciones.estudiantes.por-profesor") }}',
            type: 'GET',
            data: { profesor_id: profesorId },
            success: function(response) {
                if (response && response.success) {
                    profesorSecciones = response.secciones || [];
                    profesorEstudiantes = response.estudiantes || [];
                    renderSeccionesOptions(false);

                    // Si hay secciones disponibles, habilitar el selector (a menos que esté en modo todas)
                    $secciones.prop('disabled', $todas.is(':checked') ? true : false);
                    syncModoTodasSecciones();
                    renderEstudiantesBySelectedSecciones();
                } else {
                    setInitialTableMessage('No se pudieron cargar los estudiantes');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar estudiantes por profesor:', xhr);
                setInitialTableMessage('Error al cargar los estudiantes');
            }
        });
    }

    $todas.on('change', function() {
        syncModoTodasSecciones();
        renderEstudiantesBySelectedSecciones();
    });

    $profesor.on('change', function() {
        cargarEstudiantesPorProfesor($(this).val());
    });

    $secciones.on('change', function() {
        renderEstudiantesBySelectedSecciones();
    });

    // estado inicial
    $secciones.prop('disabled', true);
    syncModoTodasMaterias();
    syncModoTodasSecciones();
    renderEstudiantesBySelectedSecciones();

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

    function selectHalfBySeccion(whichHalf, checked) {
        var all = $('.estudiante-checkbox');
        all.prop('checked', false);

        if (!checked) {
            updateSelectAllState();
            return;
        }

        var bySeccion = {};
        all.each(function() {
            var sid = (($(this).data('seccion-id') || '') + '').toString();
            if (!bySeccion[sid]) bySeccion[sid] = [];
            bySeccion[sid].push(this);
        });

        Object.keys(bySeccion).forEach(function(sid) {
            var arr = bySeccion[sid] || [];
            var half = Math.ceil(arr.length / 2);
            if (whichHalf === 'top') {
                $(arr.slice(0, half)).prop('checked', true);
            } else {
                $(arr.slice(half)).prop('checked', true);
            }
        });

        updateSelectAllState();
    }

    // Seleccionar mitad superior
    $('#select-top-half').on('change', function() {
        var checked = $(this).is(':checked');
        selectHalfBySeccion('top', checked);
        // reset other half checkbox
        $('#select-bottom-half').prop('checked', false);
        // reset género
        $('#select-male, #select-female').prop('checked', false);
    });

    // Seleccionar mitad inferior
    $('#select-bottom-half').on('change', function() {
        var checked = $(this).is(':checked');
        selectHalfBySeccion('bottom', checked);
        // reset top half checkbox
        $('#select-top-half').prop('checked', false);
        // reset género
        $('#select-male, #select-female').prop('checked', false);
    });
});
</script>
@endpush

@stop
