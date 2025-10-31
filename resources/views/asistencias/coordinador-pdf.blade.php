<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Resumen de Inasistencias</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 10px;
            text-align: center;
        }
        h2 {
            font-size: 16px;
            margin: 20px 0 10px;
        }
        p {
            margin: 4px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
        }
        .summary-table th, .summary-table td {
            text-align: center;
        }
        .section-header {
            background: #f7f7f7;
            border: 1px solid #ccc;
            padding: 8px 10px;
            font-weight: bold;
        }
        .muted {
            color: #666;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .mb-2 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Resumen de Inasistencias por Coordinación</h1>
    <p><strong>Generado por:</strong> {{ optional($usuario)->name ?? optional($usuario)->email ?? 'Usuario' }}</p>
    <p><strong>Fecha de generación:</strong> {{ $generatedAt->format('d/m/Y H:i') }}</p>
    @php
        $startFilter = $filters['start_date'] ?? null;
        $endFilter = $filters['end_date'] ?? null;
    @endphp
    @if($startFilter || $endFilter)
        <p>
            <strong>Rango de fechas:</strong>
            @if($startFilter)
                desde {{ \Carbon\Carbon::createFromFormat('Y-m-d', $startFilter)->format('d/m/Y') }}
            @endif
            @if($startFilter && $endFilter)
                hasta
            @elseif(!$startFilter && $endFilter)
                hasta
            @endif
            @if($endFilter)
                {{ \Carbon\Carbon::createFromFormat('Y-m-d', $endFilter)->format('d/m/Y') }}
            @endif
        </p>
    @endif
    <p><strong>Total de inasistencias:</strong> {{ $totalInasistencias }}</p>
    <p><strong>Horas:</strong> {{ $totalMultiplicado }}</p>
    <p><strong>Estudiantes con inasistencias:</strong> {{ $totalEstudiantes }}</p>

    <table class="summary-table">
        <thead>
            <tr>
                <th>Grado</th>
                <th>Sección</th>
                <th>Estudiantes con inasistencias</th>
                <th>Total inasistencias</th>
                <th>Total horas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sections as $section)
                <tr>
                    <td>{{ $section['grado'] }}</td>
                    <td>{{ $section['seccion'] }}</td>
                    <td>{{ count($section['estudiantes']) }}</td>
                    <td>{{ $section['total_inasistencias'] }}</td>
                    <td>{{ $section['total_multiplicado'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-right">No existen secciones asignadas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @foreach($sections as $section)
        <div class="section-header mb-2">
            {{ $section['grado'] }} - {{ $section['seccion'] }}
            <span class="muted"> | {{ count($section['estudiantes']) }} estudiante(s) con inasistencias</span>
        </div>
        @if(count($section['estudiantes']) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th class="text-right">Inasistencias</th>
                        <th class="text-right">Horas Totales</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($section['estudiantes'] as $estudiante)
                        <tr>
                            <td>{{ $estudiante['estudiante'] }}</td>
                            <td class="text-right">{{ $estudiante['inasistencias'] }}</td>
                            <td class="text-right">{{ $estudiante['valor_doble'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="muted">Sin inasistencias registradas para esta sección.</p>
        @endif
    @endforeach
</body>
</html>
