@extends('adminlte::page')

@section('title', 'Extracurricular')

@section('content_header')
@php
    $diasHeader = [
        'Sunday' => 'Domingo',
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado'
    ];

    $mesesHeader = [
        'January' => 'Enero',
        'February' => 'Febrero',
        'March' => 'Marzo',
        'April' => 'Abril',
        'May' => 'Mayo',
        'June' => 'Junio',
        'July' => 'Julio',
        'August' => 'Agosto',
        'September' => 'Septiembre',
        'October' => 'Octubre',
        'November' => 'Noviembre',
        'December' => 'Diciembre'
    ];

    $fechaHeader = $fechaActual ?? now('America/Caracas');
    $diaHeader = $diasHeader[$fechaHeader->format('l')];
    $mesHeader = $mesesHeader[$fechaHeader->format('F')];
@endphp
<div class="d-flex justify-content-between align-items-center">
    <h2 class="mb-0">Dashboard Extracurricular</h2>
    <span class="text-muted">{{ $fechaHeader->format('d') . ' de ' . $mesHeader . ' de ' . $fechaHeader->format('Y') }}</span>
</div>
<hr class="mt-2 mb-4">
@endsection

@section('content')
@php
    $asistenciasContadas = is_numeric($asistenciasHoy ?? null) ? (int) $asistenciasHoy : 0;
    $pasesContados = is_numeric($tardiosHoy ?? null) ? (int) $tardiosHoy : 0;
    $inasistenciasContadas = is_numeric($inasistenciasHoy ?? null) ? (int) $inasistenciasHoy : 0;
    $totalProcesadas = max($asistenciasContadas + $pasesContados + $inasistenciasContadas, 1);
@endphp

<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="hero-card">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between w-100">
                    <div>
                        <p class="hero-subtitle mb-1">Asistencia extracurricular</p>
                        <h3 class="hero-title mb-3">{{ auth()->user()->name }}</h3>
                        <div class="hero-metrics">
                            <span class="hero-pill">
                                <i class="fas fa-layer-group"></i>
                                {{ $totalClases ?? 0 }} clases
                            </span>
                            <span class="hero-pill hero-pill--success">
                                <i class="fas fa-check-circle"></i>
                                {{ $asistenciasContadas }} presentes
                            </span>
                            <span class="hero-pill hero-pill--warning">
                                <i class="fas fa-clock"></i>
                                {{ $pasesContados }} pases
                            </span>
                            <span class="hero-pill hero-pill--danger">
                                <i class="fas fa-times-circle"></i>
                                {{ $inasistenciasContadas }} inasistencias
                            </span>
                        </div>
                    </div>
                    <div class="mt-4 mt-lg-0 d-flex align-items-center">
                        @if(auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('pedagogia'))
                            <a href="{{ route('extracurricular.clases.create') }}" class="btn btn-light btn-lg">
                                <i class="fas fa-plus mr-1"></i> Nueva clase
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-list text-primary mr-2"></i> Clases
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Clase</th>
                                    <th>Día</th>
                                    <th>Horario</th>
                                    <th class="text-center">Estudiantes</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $diasSemana = [
                                        1 => 'Lunes',
                                        2 => 'Martes',
                                        3 => 'Miércoles',
                                        4 => 'Jueves',
                                        5 => 'Viernes',
                                        6 => 'Sábado',
                                        7 => 'Domingo',
                                    ];
                                @endphp
                                @forelse(($clases ?? collect()) as $clase)
                                    <tr>
                                        <td>
                                            <strong>{{ $clase->nombre }}</strong>
                                            @if(!empty($clase->descripcion))
                                                <div class="text-muted small">{{ $clase->descripcion }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $diasSemana[$clase->dia_semana] ?? '—' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                @if(!empty($clase->hora_inicio) && !empty($clase->hora_fin))
                                                    {{ $clase->hora_inicio }} - {{ $clase->hora_fin }}
                                                @else
                                                    —
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary badge-pill">{{ $clase->estudiantes_count ?? 0 }}</span>
                                        </td>
                                        <td class="text-right">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('extracurricular.asistencia.form', $clase->id) }}">
                                                <i class="fas fa-clipboard-check mr-1"></i> Pasar asistencia
                                            </a>
                                            @if(auth()->user()?->hasRole('admin'))
                                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('extracurricular.clases.edit', $clase->id) }}">
                                                    <i class="fas fa-edit mr-1"></i> Editar
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No hay clases extracurriculares creadas</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('/build/assets/admin/admin.css') }}">
<style>
    .hero-card {
        position: relative;
        border-radius: 22px;
        padding: 2.25rem;
        background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        color: #fff;
        box-shadow: 0 28px 60px -35px rgba(79, 70, 229, 0.65);
        overflow: hidden;
    }
    .hero-card::after {
        content: '';
        position: absolute;
        top: -40px;
        right: -60px;
        width: 220px;
        height: 220px;
        background: rgba(255, 255, 255, 0.07);
        border-radius: 50%;
    }
    .hero-subtitle {
        text-transform: uppercase;
        letter-spacing: 0.15rem;
        font-size: 0.85rem;
        opacity: 0.8;
    }
    .hero-title {
        font-size: 2rem;
        font-weight: 700;
    }
    .hero-metrics {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.18);
        border-radius: 999px;
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
    }
    .hero-pill--success { background: rgba(52, 211, 153, 0.25); }
    .hero-pill--warning { background: rgba(251, 191, 36, 0.25); }
    .hero-pill--danger { background: rgba(248, 113, 113, 0.25); }
</style>
@endsection
