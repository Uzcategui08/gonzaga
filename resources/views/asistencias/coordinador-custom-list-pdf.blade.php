<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Lista Personalizada</title>
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
            margin-top: 14px;
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
    <h1>Registro de entrega de unidad de almacenamiento USB</h1>
    <p><strong>Generado por:</strong> {{ optional($usuario)->name ?? optional($usuario)->email ?? 'Usuario' }}</p>
    <p><strong>Sección:</strong> {{ optional($seccion->grado)->nombre ?? 'Sin grado' }} - {{ $seccion->nombre }}</p>
    

    @php
        $nameColumnWidth = $nameColumnWidth ?? 60;
        $extraFieldWidth = $extraFieldWidth ?? null;
        $customColumnWidth = $customColumnWidth ?? 30;
        $rowHeight = $rowHeight ?? 12;

        $nameHeaderStyle = $nameColumnWidth ? "width: {$nameColumnWidth}mm;" : '';
        $extraHeaderStyle = $extraFieldWidth ? "width: {$extraFieldWidth}mm;" : '';
        $customHeaderStyle = $customColumnWidth ? "width: {$customColumnWidth}mm;" : '';
        $rowHeightStyle = $rowHeight ? "height: {$rowHeight}mm;" : '';

        $nameCellStyle = trim($nameHeaderStyle . ' ' . $rowHeightStyle);
        $extraCellStyle = trim($extraHeaderStyle . ' ' . $rowHeightStyle);
        $customCellStyle = trim($customHeaderStyle . ' ' . $rowHeightStyle);
    @endphp

    <table>
        <thead>
            <tr>
                <th class="text-center" style="{{ $rowHeightStyle }}">#</th>
                <th style="{{ $nameHeaderStyle }}">Apellidos y Nombres</th>
                @if($extraFieldLabel)
                    <th style="{{ $extraHeaderStyle }}">{{ $extraFieldLabel }}</th>
                @endif
                @foreach($customColumns as $title)
                    <th style="{{ $customHeaderStyle }}">{{ $title }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                @php
                    $extraValue = null;
                    if ($extraFieldKey) {
                        $extraValue = $student->{$extraFieldKey} ?? '';
                        if ($extraFieldKey === 'fecha_nacimiento' || $extraFieldKey === 'fecha_ingreso') {
                            $extraValue = $extraValue ? \Carbon\Carbon::parse($extraValue)->format('d/m/Y') : '';
                        }
                    }
                @endphp
                <tr>
                    <td class="text-center" style="{{ $rowHeightStyle }}">{{ $index + 1 }}</td>
                    <td style="{{ $nameCellStyle }}">{{ $student->apellidos }} {{ $student->nombres }}</td>
                    @if($extraFieldLabel)
                        <td style="{{ $extraCellStyle }}">{{ $extraValue }}</td>
                    @endif
                    @foreach($customColumns as $title)
                        <td style="{{ $customCellStyle }}"></td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 2 + ($extraFieldLabel ? 1 : 0) + count($customColumns) }}" class="text-center">
                        No se encontraron estudiantes en esta sección.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
