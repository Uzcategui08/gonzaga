<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registro de Asistencia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 10px;
        }
        .container-fluid {
            width: 100%;
        }
        h2 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .col-6 {
            width: 50%;
            padding: 0 5px;
            box-sizing: border-box;
        }
        .col-12 {
            width: 100%;
            padding: 0 5px;
            box-sizing: border-box;
        }
        hr {
            border: 0;
            border-top: 1px solid #eee;
            margin: 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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
        p {
            margin: 5px 0;
        }
        strong {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2>Registro de Asistencia</h2>
        
        <div class="card">
            <div class="row">
                <div class="col-6">
                    <p><strong>Fecha y Hora de Registro:</strong> {{ $asistencia->created_at->format('d/m/Y H:i:s') }}</p>
                    <p><strong>Fecha de Clase:</strong> {{ $asistencia->fecha ? $asistencia->fecha->format('d/m/Y') : 'N/A' }}</p>
                    <p><strong>Hora de Inicio:</strong> {{ substr($asistencia->hora_inicio, 0, 5) }}</p>
                    <p><strong>Materia:</strong> {{ $asistencia->materia ? $asistencia->materia->nombre : 'N/A' }}</p>
                </div>
                <div class="col-6">
                    <p><strong>Profesor:</strong> {{ $asistencia->profesor ? ($asistencia->profesor->user ? $asistencia->profesor->user->name : 'N/A') : 'N/A' }}</p>
                    <p><strong>Aula:</strong> {{ $asistencia->horario ? $asistencia->horario->aula : 'N/A' }}</p>
                </div>
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-12">
                    <p><strong>Contenido de la Clase:</strong></p>
                    <p>{{ $asistencia->contenido_clase ?? 'N/A' }}</p>
                </div>
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-12">
                    <p><strong>Observación General:</strong></p>
                    <p>{{ $asistencia->observacion_general ?? 'N/A' }}</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <p><strong>Estudiantes:</strong></p>
                    <table>
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
                                    {{ $detalle->estado === 'P' ? 'Presente' : ($detalle->estado === 'A' ? 'Ausente' : 'Tardío') }}
                                </td>
                                <td>{{ $detalle->observacion_individual ?? 'N/A' }}</td>
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