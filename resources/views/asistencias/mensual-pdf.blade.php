<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Mensual de Asistencia</title>
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
        
        .footer {
            text-align: center;
            font-size: 8pt;
            color: #777;
            margin-top: 5mm;
            border-top: 1px solid #ddd;
            padding: 2mm 0;
        }
    </style>
</head>
<body>
    <div class="container">
            <div class="header">
            <h1>REPORTE DE ASISTENCIA</h1>
            <p>
                <strong>{{ $seccion->grado->nombre }} {{ $seccion->nombre }}</strong><br>
                <strong>Período:</strong> {{ $periodo === 'diario' ? 'Diario' : 'Mensual' }}<br>
                @if($periodo === 'diario')
                    <strong>Fecha:</strong> {{ $fecha ?? '' }}<br>
                @else
                    <strong>Mes:</strong> {{ $meses[$mes ?? ''] ?? '' }}<br>
                @endif
            </p>
        </div>

        <div class="section">

            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Asistencias</th>
                        <th>Inasistencias</th>
                        <th>Pases</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td>{{ $student->nombres }} {{ $student->apellidos }}</td>
                            <td class="present" style="color: #2e7d32;">{{ $presentes[$student->id] ?? 0 }}</td>
                            <td class="absent" style="color: #c62828;">{{ $absences[$student->id] ?? 0 }}</td>
                            <td class="pase" style="color: #f9a825;">{{ $pases[$student->id] ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="present" style="color: #2e7d32;"><strong>{{ array_sum($presentes) }}</strong></td>
                        <td class="absent" style="color: #c62828;"><strong>{{ array_sum($absences) }}</strong></td>
                        <td class="pase" style="color: #f9a825;"><strong>{{ array_sum($pases) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

            <div class="footer">
            Generado el: {{ date('d/m/Y H:i:s') }}
        </div>
    </div>
</body>
</html>
