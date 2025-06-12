<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Asistencia - {{ $asistencia->materia ? $asistencia->materia->nombre : 'Clase' }}</title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #f8f9fa;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --border-color: #dee2e6;
            --text-muted: #6c757d;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #212529;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        .subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .card {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: var(--secondary-color);
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
        }
        
        .card-body {
            padding: 15px;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .col-md-6 {
            width: 50%;
            padding: 0 10px;
            box-sizing: border-box;
        }
        
        .info-item {
            margin-bottom: 12px;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--text-muted);
            display: block;
            margin-bottom: 3px;
        }
        
        .info-value {
            font-size: 15px;
        }
        
        hr {
            border: 0;
            border-top: 1px solid var(--border-color);
            margin: 20px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th {
            background-color: var(--secondary-color);
            text-align: left;
            padding: 10px 12px;
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
        }
        
        td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .status-present {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .status-absent {
            color: var(--danger-color);
            font-weight: 600;
        }
        
        .status-late {
            color: var(--warning-color);
            font-weight: 600;
        }
        
        .text-muted {
            color: var(--text-muted);
            font-size: 12px;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 15px;
            color: var(--text-muted);
            font-size: 12px;
            border-top: 1px solid var(--border-color);
        }
        
        @media print {
            body {
                background: none;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                border-radius: 0;
            }
        }
        
        @media (max-width: 768px) {
            .col-md-6 {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Registro de Asistencia</h1>
            <div class="subtitle">{{ $asistencia->materia ? $asistencia->materia->nombre : 'Clase' }}</div>
        </div>
        
        <div class="card">
            <div class="card-header">Información General</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <span class="info-label">Fecha y Hora de Registro:</span>
                            <span class="info-value">{{ $asistencia->created_at->format('d/m/Y H:i:s') }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Fecha de Clase:</span>
                            <span class="info-value">{{ $asistencia->fecha ? $asistencia->fecha->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Hora de Inicio:</span>
                            <span class="info-value">{{ substr($asistencia->hora_inicio, 0, 5) }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <span class="info-label">Materia:</span>
                            <span class="info-value">{{ $asistencia->materia ? $asistencia->materia->nombre : 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Profesor:</span>
                            <span class="info-value">{{ $asistencia->profesor ? ($asistencia->profesor->user ? $asistencia->profesor->user->name : 'N/A') : 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Aula:</span>
                            <span class="info-value">{{ $asistencia->horario ? $asistencia->horario->aula : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">Detalles de la Clase</div>
            <div class="card-body">
                <div class="info-item">
                    <span class="info-label">Contenido de la Clase:</span>
                    <p class="info-value">{{ $asistencia->contenido_clase ?? 'No se registró contenido' }}</p>
                </div>
                
                <hr>
                
                <div class="info-item">
                    <span class="info-label">Observación General:</span>
                    <p class="info-value">{{ $asistencia->observacion_general ?? 'No hay observaciones' }}</p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">Asistencia de Estudiantes</div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th width="40%">Estudiante</th>
                            <th width="20%">Estado</th>
                            <th width="40%">Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asistencia->estudiantes as $detalle)
                        <tr>
                            <td>
                                {{ $detalle->nombres . ' ' . $detalle->apellidos }}
                                <div class="text-muted">ID: {{ $detalle->estudiante_id }}</div>
                            </td>
                            <td>
                                @if($detalle->estado === 'A')
                                    <span class="status-present">Asistente</span>
                                @elseif($detalle->estado === 'I')
                                    <span class="status-absent">Inasistente</span>
                                @else
                                    <span class="status-late">Pase</span>
                                @endif
                            </td>
                            <td>{{ $detalle->observacion_individual ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="footer">
            Documento generado el {{ now()->format('d/m/Y H:i:s') }} | Sistema de Gestión Académica
        </div>
    </div>
</body>
</html>