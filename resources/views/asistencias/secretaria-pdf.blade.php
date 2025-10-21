<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencia por Género</title>
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
        .text-center {
            text-align: center;
        }
        .muted {
            color: #666;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <h1>Reporte de Asistencia por Género</h1>

    <p><strong>Generado por:</strong> {{ optional($usuario)->name ?? optional($usuario)->email ?? 'Usuario' }}</p>
    <p><strong>Fecha de generación:</strong> {{ $generatedAt->format('d/m/Y H:i') }}</p>

    @if($startDate || $endDate)
        <p>
            <strong>Rango de fechas:</strong>
            @if($startDate)
                desde {{ $startDate->format('d/m/Y') }}
            @endif
            @if($startDate && $endDate)
                hasta
            @elseif(!$startDate && $endDate)
                hasta
            @endif
            @if($endDate)
                {{ $endDate->format('d/m/Y') }}
            @endif
        </p>
    @endif

    <p><strong>Total asistentes:</strong> {{ $totals['total'] ?? 0 }}</p>
    <p><strong>Hombres:</strong> {{ $totals['masculinos'] ?? 0 }}</p>
    <p><strong>Mujeres:</strong> {{ $totals['femeninos'] ?? 0 }}</p>

    <table>
        <thead>
            <tr>
                <th>Grado</th>
                <th>Sección</th>
                <th class="text-center">Hombres</th>
                <th class="text-center">Mujeres</th>
                <th class="text-center">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sections as $section)
                <tr>
                    <td>{{ $section['grado'] }}</td>
                    <td>{{ $section['seccion'] }}</td>
                    <td class="text-center">{{ $section['masculinos'] }}</td>
                    <td class="text-center">{{ $section['femeninos'] }}</td>
                    <td class="text-center">{{ $section['total'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center muted">No se encontraron asistentes registrados en el periodo seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
        @if($sections->isNotEmpty())
            <tfoot>
                <tr>
                    <th colspan="2" class="text-center">Totales</th>
                    <th class="text-center">{{ $totals['masculinos'] ?? 0 }}</th>
                    <th class="text-center">{{ $totals['femeninos'] ?? 0 }}</th>
                    <th class="text-center">{{ $totals['total'] ?? 0 }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
