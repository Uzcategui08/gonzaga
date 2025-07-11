@extends('adminlte::page')

@section('title', 'Nuevo Justificativo')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Nuevo Justificativo</h1>
    </div>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title mb-0">
                <i class="fas fa-user-plus text-primary mr-2"></i>
            </h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="seccion_id">Sección</label>
                        <select class="form-control select2" id="seccion_id" name="seccion_id">
                            <option value="">Seleccionar sección...</option>
                            @foreach($secciones as $seccion)
                                <option value="{{ $seccion->id }}">
                                    {{ $seccion->grado->nombre }} - {{ $seccion->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="estudiante_id">Estudiante</label>
                        <select class="form-control select2" id="estudiante_id" name="estudiante_id" style="width: 100%">
                            <option value="">Seleccionar estudiante...</option>
                            @foreach($estudiantes as $estudiante)
                                <option value="{{ $estudiante->id }}" data-seccion="{{ $estudiante->seccion_id }}" data-nombre="{{ $estudiante->nombres }} {{ $estudiante->apellidos }}">
                                    {{ $estudiante->nombres }} {{ $estudiante->apellidos }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-primary" 
                                onclick="window.location.href = '/justificativos/nuevo/' + document.getElementById('estudiante_id').value"
                                id="btnContinuar">
                            <i class="fas fa-arrow-right mr-1"></i> Continuar
                        </button>
                        </a>
                        <a href="{{ route('justificativos.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
    <script>
        $(document).ready(function() {
            const estudiantesSelect = $('#estudiante_id');
            estudiantesSelect.select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Seleccionar estudiante...'
            });

            const todasLasOpciones = estudiantesSelect.find('option').clone();

            function filtrarEstudiantes() {
                const seccionSeleccionada = $('#seccion_id').val();

                estudiantesSelect.empty();
                estudiantesSelect.append('<option value="">Seleccionar estudiante...</option>');

                todasLasOpciones.each(function() {
                    const seccionEstudiante = $(this).data('seccion');
                    if (!seccionSeleccionada || seccionEstudiante == seccionSeleccionada) {
                        estudiantesSelect.append($(this).clone());
                    }
                });

                estudiantesSelect.trigger('change');
            }

            $('#seccion_id').on('change', function() {
                filtrarEstudiantes();
            });

            $('#estudiante_id').on('change', function() {
                $('#btnContinuar').prop('disabled', $(this).val() === '');
            });

            filtrarEstudiantes();
        });
    </script>
@endsection

@stop
