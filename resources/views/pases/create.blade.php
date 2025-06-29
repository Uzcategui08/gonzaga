@extends('adminlte::page')

@section('title', 'Crear Pase de Entrada')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Crear Pase de Entrada</h1>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title">
                <i class="fas fa-plus-circle text-primary mr-2"></i>
            </h3>
        </div>

        <div class="card-body">
            <form action="{{ route('pases.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estudiante_id">Estudiante <span class="text-danger">*</span></label>
                            <select id="estudiante_id" class="form-control select2" name="estudiante_id" required>
                                <option value="">Seleccionar estudiante...</option>
                                @foreach($estudiantes as $estudiante)
                                    <option value="{{ $estudiante->id }}" {{ old('estudiante_id') == $estudiante->id ? 'selected' : '' }}>
                                        {{ $estudiante->nombres }} {{ $estudiante->apellidos }} - {{ $estudiante->seccion->nombre ?? 'Sin sección' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="horario_id">Horario <span class="text-danger">*</span></label>
                            <select id="horario_id" class="form-control select2" name="horario_id" required>
                                <option value="">Seleccionar horario...</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha">Fecha <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha" name="fecha" 
                                   value="{{ old('fecha', date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="hora_llegada">Hora de Llegada <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="hora_llegada" name="hora_llegada" 
                                   value="{{ old('hora_llegada') }}" required>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="motivo">Motivo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="motivo" name="motivo" 
                                   value="{{ old('motivo') }}" required 
                                   placeholder="Ej: Transporte público, tráfico, etc.">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mb-4">
                            <label for="observaciones" class="font-weight-bold text-gray-700">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" rows="3" 
                                      class="form-control form-control-lg" 
                                      placeholder="Observaciones adicionales">{{ old('observaciones') }}</textarea>
                        </div>

                        <div class="form-group mb-4">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="aprobado" name="aprobado" value="1">
                                <label class="custom-control-label" for="aprobado">Aprobar pase</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="d-flex justify-content-start">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </button>
                        <a href="{{ route('pases.index') }}" class="btn btn-secondary">
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
    <style>
        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
            padding: .375rem .75rem !important;
        }
        #horario_id {
            display: none;
        }
        .form-control {
            height: calc(2.25rem + 2px) !important;
            font-size: 1rem;
            padding: .5rem .75rem;
            border-radius: .25rem;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .form-control-lg {
            height: calc(2.5rem + 2px) !important;
            padding: .75rem 1rem;
            font-size: 1.25rem;
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {

            $('#estudiante_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Seleccionar estudiante...',
                width: '100%'
            });
            $('#horario_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Seleccionar horario...',
                width: '100%'
            });

            $('#hora_llegada').attr('min', '08:00');

            $('#fecha').attr('max', new Date().toISOString().split('T')[0]);
            
            $('#estudiante_id').on('change', function() {
                const estudianteId = $(this).val();
                
                if (estudianteId) {
                    $.ajax({
                        url: `/estudiantes/${estudianteId}/horarios`,
                        method: 'GET',
                        success: function(response) {
                            $('#horario_id').empty().append('<option value="">Seleccionar horario...</option>');

                            if (response.horarios && response.horarios.length > 0) {
                                response.horarios.forEach(horario => {
                                    $('#horario_id').append(
                                        $('<option></option>')
                                            .attr('value', horario.id)
                                            .text(`${horario.asignacion.materia.nombre} - ${horario.hora_inicio} a ${horario.hora_fin}`)
                                    );
                                });
                                $('#horario_id').show();
                            } else {
                                $('#horario_id').append(
                                    $('<option></option>')
                                        .attr('value', '')
                                        .text('No hay horarios disponibles para este estudiante')
                                );
                                $('#horario_id').show();
                            }
                        },
                        error: function(xhr, status, error) {
                            $('#horario_id').append(
                                $('<option></option>')
                                    .attr('value', '')
                                    .text('Error al cargar los horarios')
                            );
                            $('#horario_id').show();
                        }
                    });
                } else {
                    $('#horario_id').empty().append('<option value="">Seleccionar horario...</option>');
                    $('#horario_id').hide();
                }
            });

            $('#horario_id').on('change', function() {

            });

            const estudianteSeleccionado = $('#estudiante_id').val();
            if (estudianteSeleccionado) {
                $('#estudiante_id').trigger('change');
            }
        });
    </script>
@stop