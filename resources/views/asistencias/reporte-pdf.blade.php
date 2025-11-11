<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencias</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
                                                                @php
                                                                    $generoTexto = strtolower(trim((string) ($detalle->genero ?? '')));
                                                                    if (in_array($generoTexto, ['f', 'femenino', 'female'])) {
                                                                        $generoLabel = 'Femenino';
                                                                    } elseif (in_array($generoTexto, ['m', 'masculino', 'male'])) {
                                                                        $generoLabel = 'Masculino';
                                                                    } else {
                                                                        $generoLabel = 'Género no especificado';
                                                                    }
                                                                @endphp
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .table-bordered {
            border: 1px solid #ddd;
        }
        .table-responsive {
            overflow-x: auto;
        }
        h2 {
            font-size: 18px;
            margin-bottom: 15px;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -10px;
            margin-left: -10px;
        }
        .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
            padding-right: 10px;
            padding-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h2 class="text-center mb-4">Reporte de Asistencias</h2>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Materia</th>
                                <th>Profesor</th>
                                <th>Contenido</th>
                                <th>Observación</th>
                                <th>Estudiantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asistencias as $asistencia)
                                <tr>
                                    <td>{{ $asistencia->fecha ? $asistencia->fecha->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $asistencia->hora_inicio ?? 'N/A' }}</td>
                                    <td>{{ $asistencia->materia ? $asistencia->materia->nombre : 'N/A' }}</td>
                                    <td>
                                        @php
                                            $profesor = $asistencia->profesor;
                                            echo $profesor ? ($profesor->user ? $profesor->user->name : 'N/A') : 'N/A';
                                        @endphp
                                    </td>
                                    <td>{{ $asistencia->contenido_clase ?? 'N/A' }}</td>
                                    <td>{{ $asistencia->observacion_general ?? 'N/A' }}</td>
                                    <td>
                                        @if($asistencia->estudiantes && $asistencia->estudiantes->count() > 0)
                                            <table class="table table-bordered" style="margin: 0; font-size: 11px;">
                                                <thead>
                                                    <tr>
                                                        <th>Estudiante</th>
                                                        <th>Estado</th>
                                                        <th>Observación</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($asistencia->estudiantes as $detalle)
                                                        <tr>
                                                            <td>
                                                                {{ $detalle->nombres . ' ' . $detalle->apellidos }}
                                                                <br>
                                                                <small class="text-muted">(ID: {{ $detalle->estudiante_id }})</small>
                                                            </td>
                                                            <td>
                                                                {{ $detalle->estado === 'A' ? 'Asistente' : ($detalle->estado === 'I' ? 'Inasistente' : 'Pase') }}
                                                            </td>
                                                            <td>{{ $detalle->observacion_individual ?? 'N/A' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            No hay estudiantes registrados
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>