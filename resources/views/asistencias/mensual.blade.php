@extends('adminlte::page')

@section('title', 'Registro de Asistencia')

@section('content_header')
    <h1>Registro de Asistencia</h1>
@stop

@section('js')
<style>
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .form-label {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .form-control-lg:disabled,
    .select2:disabled,
    select:disabled {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
        opacity: 0.7 !important;
        border-color: #ced4da !important;
        cursor: not-allowed !important;
    }

    .form-control-lg:disabled:focus,
    .select2:disabled:focus,
    select:disabled:focus {
        box-shadow: none !important;
        border-color: #ced4da !important;
    }

    .form-control-lg:required,
    .select2:required,
    select:required {
        border-color: #ced4da !important;
    }

    .form-control-lg:required:focus,
    .select2:required:focus,
    select:required:focus {
        border-color: #ced4da !important;
        box-shadow: none !important;
    }

    select.form-control-lg {
        background-color: inherit;
        color: inherit;
        border-color: #ced4da !important;
    }

    select.form-control-lg:disabled {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
        opacity: 0.7 !important;
        border-color: #ced4da !important;
    }

    select.form-control-lg:required {
        border-color: #ced4da !important;
    }

    select.form-control-lg:required:focus {
        border-color: #ced4da !important;
        box-shadow: none !important;
    }

    input.form-control-lg {
        background-color: inherit;
        color: inherit;
        border-color: #ced4da !important;
    }

    input.form-control-lg:disabled {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
        opacity: 0.7 !important;
        border-color: #ced4da !important;
    }

    input.form-control-lg:required {
        border-color: #ced4da !important;
    }

    input.form-control-lg:required:focus {
        border-color: #ced4da !important;
        box-shadow: none !important;
    }
</style>
<script>
    function handleSectionChange(select) {
        const periodoSelect = document.getElementById('periodo');
        const fechaInput = document.getElementById('fecha');
        const mesSelect = document.getElementById('mes');

        const seccionId = select.value;
        if (seccionId) {
            periodoSelect.disabled = false;
            periodoSelect.required = true;

            handlePeriodoChange(periodoSelect);
        } else {
            periodoSelect.disabled = true;
            periodoSelect.required = false;
            fechaInput.disabled = true;
            fechaInput.required = false;
            mesSelect.disabled = true;
            mesSelect.required = false;
        }
    }

    function handlePeriodoChange(select) {
        const periodo = select.value;
        const fechaInput = document.getElementById('fecha');
        const mesSelect = document.getElementById('mes');

        if (periodo === 'diario') {
            fechaInput.disabled = false;
            fechaInput.required = true;
            mesSelect.disabled = true;
            mesSelect.required = false;
        } else if (periodo === 'mensual') {
            fechaInput.disabled = true;
            fechaInput.required = false;
            mesSelect.disabled = false;
            mesSelect.required = true;
        }
    }

    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            theme: 'bootstrap4'
        });

        handleSectionChange(document.getElementById('seccion_id'));
        handlePeriodoChange(document.getElementById('periodo'));
    });

    function clearFilters() {
        const form = document.querySelector('form');
        form.reset();

        const select2Elements = document.querySelectorAll('.select2');
        select2Elements.forEach(element => {
            element.value = '';
            element.dispatchEvent(new Event('change'));
        });
        
        window.location.href = '{{ route('asistencia.mensual.index') }}';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const periodoSelect = document.getElementById('periodo');
        const fechaInput = document.getElementById('fecha');
        const mesInput = document.getElementById('mes');
        
        if (!periodoSelect || !fechaInput || !mesInput) {
            console.error('Elementos del formulario no encontrados');
            return;
        }

        fechaInput.disabled = true;
        mesInput.disabled = true;

        if (periodoSelect.value) {
            toggleFields(periodoSelect);
        }

        periodoSelect.addEventListener('change', function() {
            toggleFields(this);
        });
    });

    function handleFormSubmit() {
        const form = document.getElementById('attendanceForm');
        form.submit();
    }

    function generatePDF() {
        const seccion = document.getElementById('seccion_id');
        const periodo = document.getElementById('periodo');
        const fecha = document.getElementById('fecha');
        const mes = document.getElementById('mes');

        if (!seccion.value || !periodo.value) {
            alert('Por favor seleccione sección y período.');
            return;
        }

        if (periodo.value === 'diario' && !fecha.value) {
            alert('Por favor, seleccione una fecha para el período diario');
            return;
        }

        const url = new URL('{{ route('asistencia.mensual.pdf') }}');

        url.searchParams.append('seccion_id', seccion.value);
        url.searchParams.append('periodo', periodo.value);
        if (fecha.value) {
            url.searchParams.append('fecha', fecha.value);
        }
        if (mes.value) {
            url.searchParams.append('mes', mes.value);
        }

        window.open(url.toString(), '_blank');
    }

    document.getElementById('seccion_id').addEventListener('change', function() {
        document.getElementById('asignacion_id').value = '';
    });

    document.addEventListener('DOMContentLoaded', function() {
        const periodo = document.getElementById('periodo');
        const fecha = document.getElementById('fecha');
        const mes = document.getElementById('mes');

        updateFieldsBasedOnPeriod(periodo.value);
    });

    document.getElementById('periodo').addEventListener('change', function() {
        updateFieldsBasedOnPeriod(this.value);
    });

    function updateFieldsBasedOnPeriod(period) {
        const fecha = document.getElementById('fecha');
        const mes = document.getElementById('mes');
        
        if (period === 'diario') {
            fecha.disabled = false;
            mes.disabled = true;
            mes.value = '';
        } else if (period === 'mensual') {
            fecha.disabled = true;
            fecha.value = '';
            mes.disabled = false;
        } else {
            fecha.disabled = true;
            fecha.value = '';
            mes.disabled = true;
            mes.value = '';
        }
    }

</script>

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Reporte de Asistencia</h3>
                </div>
                <div class="card-body">
                    <form id="attendanceForm" method="GET" class="mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="seccion_id" class="form-label">Sección *</label>
                                    <select name="seccion_id" id="seccion_id" class="form-control-lg select2">
                                        <option value="">Seleccione una sección</option>
                                        @foreach($secciones as $seccion)
                                            <option value="{{ $seccion->id }}" {{ $selectedSeccion == $seccion->id ? 'selected' : '' }}>
                                                {{ $seccion->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="periodo" class="form-label">Periodo *</label>
                                    <select name="periodo" id="periodo" class="form-control-lg select2" onchange="handlePeriodoChange(this)">
                                        <option value="">Seleccione un periodo</option>
                                        <option value="diario" {{ request('periodo') == 'diario' ? 'selected' : '' }}>Diario</option>
                                        <option value="mensual" {{ request('periodo') == 'mensual' ? 'selected' : '' }}>Mensual</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fecha" class="form-label">Fecha</label>
                                    <input type="date" name="fecha" id="fecha" disabled class="form-control-lg" 
                                           value="{{ request('fecha') }}" 
                                           onchange="handleFormSubmit()">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="mes">Mes</label>
                                    <select class="form-control-lg" id="mes" name="mes" disabled>
                                        <option value="">Seleccione un mes</option>
                                        @foreach($meses as $numero => $nombre)
                                            <option value="{{ $numero }}" {{ request('mes') == $numero ? 'selected' : '' }}>
                                                {{ $nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary w-100" style="height: 46px;" onclick="generatePDF()">Generar PDF</button>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <button type="button" class="btn btn-secondary w-100" style="height: 46px;" onclick="clearFilters()">Limpiar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
