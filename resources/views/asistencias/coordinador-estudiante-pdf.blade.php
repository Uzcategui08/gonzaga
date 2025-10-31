<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Detalle de Inasistencias</title>
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
            margin: 16px 0 8px;
        }
        p {
            margin: 4px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
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
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .muted {
            color: #666;
            font-size: 11px;
        }
        .summary-box {
            margin-top: 12px;
        }
        .summary-box span {
            display: inline-block;
            margin-right: 16px;
        }
        .no-data {
            margin-top: 16px;
            font-style: italic;
            color: #777;
        }
    </style>
</head>
<body>
    <h1>Detalle de Inasistencias</h1>
    <p><strong>Generado por:</strong> {{ optional($usuario)->name ?? optional($usuario)->email ?? 'Usuario' }}</p>
    <p><strong>Fecha de generación:</strong> {{ $generatedAt->format('d/m/Y H:i') }}</p>

    <h2>Información del Estudiante</h2>
    <p><strong>Estudiante:</strong> {{ $estudiante->apellidos }} {{ $estudiante->nombres }} (ID {{ $estudiante->id }})</p>
    <p><strong>Sección:</strong> {{ optional($seccion->grado)->nombre ?? 'Sin grado' }} - {{ $seccion->nombre }}</p>

    @php
        $startFilter = $filters['start_date'] ?? null;
        $endFilter = $filters['end_date'] ?? null;
        $weekFilter = $filters['week'] ?? null;
        $referenceFilter = $filters['reference_date'] ?? null;
        $dayNames = [
            'monday' => 'Lunes',
            'tuesday' => 'Martes',
            'wednesday' => 'Miércoles',
            'thursday' => 'Jueves',
            'friday' => 'Viernes',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo',
        ];
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

    @if($weekFilter)
        @php
            $weekYear = substr($weekFilter, 0, 4);
            $weekNumber = ltrim(substr($weekFilter, -2), '0');
        @endphp
        <p><strong>Semana:</strong> {{ $weekNumber }} / {{ $weekYear }}</p>
    @endif

    @if($referenceFilter)
        <p><strong>Fecha seleccionada:</strong> {{ \Carbon\Carbon::createFromFormat('Y-m-d', $referenceFilter)->format('d/m/Y') }}</p>
    @endif

    <div class="summary-box">
        <span><strong>Total de registros:</strong> {{ count($detalles) }}</span>
        <span><strong>Pases (P):</strong> {{ $totales['P'] ?? 0 }}</span>
        <span><strong>Asistencias (A):</strong> {{ $totales['A'] ?? 0 }}</span>
        <span><strong>Inasistencias (I):</strong> {{ $totales['I'] ?? 0 }}</span>
        <span><strong>Horas faltadas:</strong> {{ $totalHoras }}</span>
    </div>

    @if(count($detalles) > 0)
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Día</th>
                    <th>Horario</th>
                    <th>Materia</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Horas faltadas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalles as $detalle)
                    @php
                        $fechaDetalle = $detalle['fecha'] instanceof \Illuminate\Support\Carbon ? $detalle['fecha'] : \Carbon\Carbon::parse($detalle['fecha']);
                        $horaInicio = $detalle['hora_inicio'] ?? 'N/A';
                        $horaFin = $detalle['hora_fin'] ?? null;
                        $horarioTexto = $horaFin ? $horaInicio . ' - ' . $horaFin : $horaInicio;
                        $dayKey = strtolower($fechaDetalle->format('l'));
                        $diaTexto = $dayNames[$dayKey] ?? ucfirst($dayKey);
                    @endphp
                    <tr>
                        <td>{{ $fechaDetalle->format('d/m/Y') }}</td>
                        <td>{{ $diaTexto }}</td>
                        <td>{{ $horarioTexto }}</td>
                        <td>{{ $detalle['materia'] }}</td>
                        <td class="text-center">{{ $detalle['estado'] }}</td>
                        <td class="text-center">{{ $detalle['horas_inasistencia'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="no-data">No se registran clases para este estudiante en el rango seleccionado.</p>
    @endif
</body>
</html>
