<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Asistencia - {{ $asistencia->materia ? $asistencia->materia->nombre : 'Clase' }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 5mm;
        }
        
        .container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 4mm;
            padding-bottom: 3mm;
            border-bottom: 1px solid #ddd;
        }
        
        h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 2mm 0;
            color: #222;
        }
        
        .subtitle {
            font-size: 11pt;
            font-weight: normal;
            margin: 0;
            color: #555;
        }
        
        .info-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3mm;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .info-group {
            display: flex;
            gap: 8mm;
            margin-bottom: 2mm;
            align-items: center;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            white-space: nowrap;
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: bold;
            margin-right: 2mm;
            font-size: 9pt;
            color: #555;
        }
        
        .info-value {
            font-size: 10pt;
        }
        
        .section {
            margin-bottom: 4mm;
        }
        
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            padding-bottom: 1mm;
            margin-bottom: 2mm;
        }
        
        .section-content {
            font-size: 10pt;
            padding-left: 2mm;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2mm;
            font-size: 9pt;
            page-break-inside: avoid;
        }
        
        th {
            text-align: left;
            padding: 1.5mm 2mm;
            background-color: #f5f5f5;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        
        td {
            padding: 1.5mm 2mm;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .status {
            font-weight: bold;
        }
        
        .present {
            color: #2e7d32;
        }
        
        .absent {
            color: #c62828;
        }
        
        .late {
            color: #f9a825;
        }
        
        .student-id {
            font-size: 8pt;
            color: #777;
        }
        
        .footer {
            text-align: center;
            font-size: 8pt;
            color: #777;
            margin-top: 5mm;
            border-top: 1px solid #ddd;
            padding-top: 2mm;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        .compact-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            table-layout: fixed;
        }

        .compact-info td {
            padding: 1mm 2mm;
            vertical-align: top;
            border: none;
        }

        .compact-label {
            font-weight: bold;
            font-size: 9pt;
            color: #555;
            white-space: nowrap;
            display: block; 
        }

        .compact-value {
            font-size: 10pt;
            padding-right: 5mm;
            display: block; 
            word-wrap: break-word; 
        }

        .separator {
            color: #ddd;
            padding: 0 2mm;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        if (!isset($asistencia) && isset($asistenciaData)) {
            $asistencia = json_decode(json_encode($asistenciaData));
        }
    @endphp
    <div class="container">
        <div class="header">
            <h1>REGISTRO DE ASISTENCIA</h1>
            <p class="subtitle">{{ $asistencia->materia ? $asistencia->materia->nombre : 'Clase' }}</p>
            <p class="subtitle">{{ $asistencia->grado?->nombre ?? 'No especificado' }} - {{ $asistencia->seccion?->asignacion?->seccion?->nombre ?? 'No especificada' }}</p>
        </div>
        
        <table class="compact-info avoid-break" style="table-layout:fixed;">
            <tr>
                <td style="width:16%;">
                    <span class="compact-label">Fecha clase:</span>
                    <span class="compact-value">{{ $asistencia->fecha ? $asistencia->fecha->format('d/m/Y') : 'N/A' }}</span>
                </td>
                <td class="separator" style="width:2%;">|</td>
                <td style="width:12%;">
                    <span class="compact-label">Hora:</span>
                    <span class="compact-value">{{ substr($asistencia->hora_inicio, 0, 5) }}</span>
                </td>
                <td class="separator" style="width:2%;">|</td>
                <td style="width:13%;">
                    <span class="compact-label">Aula:</span>
                    <span class="compact-value">{{ $asistencia->horario ? $asistencia->horario->aula : 'N/A' }}</span>
                </td>
                <td class="separator" style="width:2%;">|</td>
                <td style="width:22%;">
                    <span class="compact-label">Profesor:</span>
                    <span class="compact-value">{{ $asistencia->profesor ? ($asistencia->profesor->user ? $asistencia->profesor->user->name : 'N/A') : 'N/A' }}</span>
                </td>
                <td class="separator" style="width:2%;">|</td>
                <td style="width:21%;">
                    <span class="compact-label">Registro:</span>
                    <span class="compact-value">{{ $asistencia->created_at->format('d/m/Y H:i') }}</span>
                </td>
            </tr>
        </table>

        <div class="section avoid-break">
            <div class="section-title">DETALLES DE LA CLASE</div>
            <table class="compact-info" style="table-layout:fixed; width:100%;">
                <tr>
                    <td style="width:49%; vertical-align: top;">
                        <div style="display: block;">
                            <span class="compact-label">Contenido:</span>
                            <div class="compact-value" style="display: block; margin-top: 2px;">{{ $asistencia->contenido_clase ?? 'No se registró contenido' }}</div>
                        </div>
                    </td>
                    <td class="separator" style="width:2%; vertical-align: top;">|</td>
                    <td style="width:49%; vertical-align: top;">
                        <div style="display: block;">
                            <span class="compact-label">Observaciones:</span>
                            <div class="compact-value" style="display: block; margin-top: 2px;">{{ $asistencia->observacion_general ?? 'No hay observaciones' }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="section avoid-break">
            <div class="section-title">LISTA DE ESTUDIANTES</div>
            <table>
                <thead>
                    <tr>
                        <th width="50%">Estudiante</th>
                        <th width="15%">Asistencia</th>
                        <th width="35%">Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asistencia->estudiantes as $detalle)
                    <tr>
                        <td>
                            @php
                                $estudiante = $detalle->estudiante ?? null;
                                $nombreCompleto = $estudiante
                                    ? ($estudiante->nombres . ' ' . $estudiante->apellidos)
                                    : ($detalle->nombres . ' ' . $detalle->apellidos);
                                $generoRaw = $estudiante?->genero ?? $detalle->genero ?? '';
                                $generoTexto = strtolower(trim((string) $generoRaw));
                                if (in_array($generoTexto, ['f', 'femenino', 'female'])) {
                                    $generoLabel = 'Femenino';
                                } elseif (in_array($generoTexto, ['m', 'masculino', 'male'])) {
                                    $generoLabel = 'Masculino';
                                } else {
                                    $generoLabel = 'Género no especificado';
                                }
                            @endphp
                            {{ $nombreCompleto }}
                            <div class="student-id">ID: {{ $detalle->estudiante_id }} | {{ $generoLabel }}</div>
                        </td>
                        <td>
                            @if($detalle->estado === 'A')
                                <span class="status present">Asistió</span>
                            @elseif($detalle->estado === 'I')
                                <span class="status absent">Faltó</span>
                            @else
                                <span class="status late">Pase</span>
                            @endif
                        </td>
                        <td>{{ $detalle->observacion_individual ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="footer">
            Documento generado el {{ now()->format('d/m/Y H:i') }} | Sistema de Gestión Académica
        </div>
    </div>
</body>
</html>