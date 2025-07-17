<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Clase</title>
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
    <div class="container">
        <div class="header">
            <h1>NOTA DE CLASE</h1>
        </div>
        
        <div class="section">
            <div class="section-title">DETALLES DE LA CLASE</div>
            <table class="compact-info">
                <tr>
                    <td style="width: 15%;">
                        <span class="compact-label">Fecha:</span>
                        <span class="compact-value">{{ \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y') }}</span>
                    </td>
                    <td class="separator">|</td>
                    <td style="width: 20%;">
                        <span class="compact-label">Hora:</span>
                        <span class="compact-value">{{ $asistencia->horario->hora_inicio }}</span>
                    </td>
                    <td class="separator">|</td>
                    <td style="width: 10%;">
                        <span class="compact-label">Año:</span>
                        <span class="compact-value">{{ $asistencia->horario->asignacion->seccion->grado->nombre }}</span>
                    </td>
                    <td class="separator">|</td>
                    <td style="width: 10%;">
                        <span class="compact-label">Sección:</span>
                        <span class="compact-value">{{ $asistencia->horario->asignacion->seccion->nombre }}</span>
                    </td>
                    <td class="separator">|</td>
                    <td style="width: 10%;">
                        <span class="compact-label">Aula:</span>
                        <span class="compact-value">{{ $asistencia->horario->aula }}</span>
                    </td>
                    <td class="separator">|</td>
                    <td style="width: 25%;">
                        <span class="compact-label">Materia:</span>
                        <span class="compact-value">{{ $asistencia->horario->asignacion->materia->nombre }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">NOTA DEL PROFESOR</div>
            <div class="section-content">
                {{ $asistencia->profesor_observacion }}
            </div>
        </div>

        <div class="footer">
            Documento generado el {{ now()->format('d/m/Y H:i') }} | Sistema de Gestión Académica
        </div>
    </div>
</body>
</html>
