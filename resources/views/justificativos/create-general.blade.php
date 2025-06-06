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

        <div class="card-body p-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="estudiante_id" class="font-weight-bold">Seleccionar Estudiante</label>
                        <select id="estudiante_id" class="form-control select2" name="estudiante_id" required>
                            <option value="">Seleccionar estudiante...</option>
                            @foreach($estudiantes as $estudiante)
                                <option value="{{ $estudiante->id }}">
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
        // Inicializar Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Seleccionar estudiante...',
            width: '100%'
        });

        // Deshabilitar botón de continuar hasta que se seleccione un estudiante
        $('#estudiante_id').on('change', function() {
            $('#btnContinuar').prop('disabled', $(this).val() === '');
        }).trigger('change');
    });
</script>
@endsection

@stop
