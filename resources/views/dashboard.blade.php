@extends('adminlte::page')

@section('title', 'Inicio')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    @php
        $dias = [
            'Sunday' => 'Domingo',
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado'
        ];
        
        $meses = [
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

        $fecha = now('America/Caracas');
        $dia = $dias[$fecha->format('l')];
        $mes = $meses[$fecha->format('F')];
    @endphp
    <h2 class="mb-0">Dashboard</h2>
    <span class="text-muted">{{ $fecha->format('d') . ' de ' . $mes . ' de ' . $fecha->format('Y') }}</span>
</div>
<hr class="mt-2 mb-4">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white border-0 py-3">
                    <h3 class="card-title mb-0 d-flex align-items-center">
                        <i class="fas fa-user text-primary mr-2"></i>
                        Bienvenido
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 font-weight-bold">
                                @if(auth()->user()->hasRole('profesor'))
                                    Profesor {{ auth()->user()->name }}
                                @elseif(auth()->user()->hasRole('admin'))
                                    Administrador {{ auth()->user()->name }}
                                @elseif(auth()->user()->hasRole('coordinador'))
                                    Coordinador {{ auth()->user()->name }}
                                @else
                                    {{ auth()->user()->name }}
                                @endif
                            </h4>
                            <p class="mb-0">Aquí puedes gestionar todas tus actividades</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->hasRole('profesor'))
        <div class="row">
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="card card-statistic">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-calendar-alt text-white"></i>
                            </div>
                            <div class="ml-3">
                                <h2 class="mb-0">{{ $totalClases ?? 0 }}</h2>
                                <p class="mb-0 text-muted">Clases Programadas</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress progress-xs">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                            </div>
                            <small class="text-muted">Total día actual</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="card card-statistic">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-success">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                            <div class="ml-3">
                                <h2 class="mb-0">{{ $clasesConAsistencia ?? 0 }}</h2>
                                <p class="mb-0 text-muted">Clases con Asistencia</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress progress-xs">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $porcentajeAsistencia ?? 0 }}%"></div>
                            </div>
                            <small class="text-muted">{{ $porcentajeAsistencia ?? 0 }}% completado</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="card card-statistic">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <div class="ml-3">
                                <h2 class="mb-0">{{ ($totalClases ?? 0) - ($clasesConAsistencia ?? 0) }}</h2>
                                <p class="mb-0 text-muted">Clases Pendientes</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress progress-xs">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($totalClases ? ($totalClases - ($clasesConAsistencia ?? 0)) / $totalClases * 100 : 0) }}%"></div>
                            </div>
                            <small class="text-muted">Por registrar</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="card card-statistic">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-exclamation-circle text-white"></i>
                            </div>
                            <div class="ml-3">
                                <h2 class="mb-0">{{ $inasistenciasProfesor ?? 0 }}</h2>
                                <p class="mb-0 text-muted">Inasistencias</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress progress-xs">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ ($totalEstudiantesProfesor ? ($inasistenciasProfesor / $totalEstudiantesProfesor * 100) : 0) }}%"></div>
                            </div>
                            <small class="text-muted">Estudiante(s) hoy</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="card-title mb-0 d-flex align-items-center">
                            <i class="fas fa-user-graduate text-primary mr-2"></i>
                            Estudiantes
                        </h3>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <h2 class="mb-0">{{ $totalEstudiantes }}</h2>
                            </div>
                            <div>
                                <p class="mb-0">Total</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="card-title mb-0 d-flex align-items-center">
                            <i class="fas fa-chalkboard-teacher text-primary mr-2"></i>
                            Profesores
                        </h3>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <h2 class="mb-0">{{ $totalProfesores }}</h2>
                            </div>
                            <div>
                                <p class="mb-0">Total</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="card-title mb-0 d-flex align-items-center">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            Asistencias Hoy
                        </h3>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <h2 class="mb-0 text-success">{{ $asistenciasHoy }}</h2>
                            </div>
                            <div>
                                <p class="mb-0 text-success">Total</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="card-title mb-0 d-flex align-items-center">
                            <i class="fas fa-times-circle text-danger mr-2"></i>
                            Inasistencias Hoy
                        </h3>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <h2 class="mb-0 text-danger">{{ $inasistenciasHoy }}</h2>
                            </div>
                            <div>
                                <p class="mb-0 text-danger">Total</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="card-title mb-0 d-flex align-items-center">
                            <i class="fas fa-clipboard-check text-primary mr-2"></i>
                            Resumen de Asistencias Hoy
                        </h3>
                    </div>
                    <div class="card-body">
                        @if((auth()->user()->hasRole('coordinador') || auth()->user()->hasRole('admin')) && ($asistenciasHoy > 0 || $inasistenciasHoy > 0 || $tardiosHoy > 0))
                            <div class="d-flex justify-content-center mb-3">
                                <canvas id="attendanceSummaryChart" style="height: 200px;"></canvas>
                            </div>
                            <div class="row mt-3">
                                <div class="col-4">
                                    <div class="text-center">
                                        <i class="fas fa-check-circle text-success mb-1" style="font-size: 24px;"></i>
                                        <h4 class="font-weight-bold mb-0">{{ $asistenciasHoy }}</h4>
                                        <p class="text-muted mb-0">Asistentes</p>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center">
                                        <i class="fas fa-clock text-warning mb-1" style="font-size: 24px;"></i>
                                        <h4 class="font-weight-bold mb-0">{{ $tardiosHoy }}</h4>
                                        <p class="text-muted mb-0">Pases</p>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center">
                                        <i class="fas fa-times-circle text-danger mb-1" style="font-size: 24px;"></i>
                                        <h4 class="font-weight-bold mb-0">{{ $inasistenciasHoy }}</h4>
                                        <p class="text-muted mb-0">Inasistentes</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($asistenciasHoy / ($asistenciasHoy + $tardiosHoy + $inasistenciasHoy)) * 100 }}%"></div>
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($tardiosHoy / ($asistenciasHoy + $tardiosHoy + $inasistenciasHoy)) * 100 }}%"></div>
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ ($inasistenciasHoy / ($asistenciasHoy + $tardiosHoy + $inasistenciasHoy)) * 100 }}%"></div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <p class="text-muted">No hay registros de asistencia para hoy</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user()->hasRole('profesor') && isset($horarioHoy))
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="card-title mb-0 d-flex align-items-center">
                            <i class="fas fa-calendar-day text-primary mr-2"></i>
                            <span>Horario de Hoy</span>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-nowrap">Hora</th>
                                        <th>Asignatura</th>
                                        <th>Grupo</th>
                                        <th>Aula</th>
                                        <th>Sección</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($horarioHoy as $clase)
                                        @php
                                            $fechaActual = now('America/Caracas');
                                            $asistencia = $asistenciasHoy->filter(function($a) use ($clase, $fechaActual) {
                                                return $a->fecha->toDateString() === $fechaActual->toDateString() && 
                                                       $a->horario_id === $clase->id;
                                            })->first();
                                        @endphp
                                        <tr class="{{ $asistencia ? 'asistencia-tomada' : '' }}">
                                            <td class="text-nowrap">
                                                <span class="badge bg-light rounded-pill px-4 py-2 text-dark">
                                                    {{ $clase->hora_inicio }} - {{ $clase->hora_fin }}
                                                </span>
                                            </td>
                                            <td>{{ $clase->asignacion->materia->nombre }}</td>
                                            <td>
                                                <span class="badge bg-primary rounded-pill px-4 py-2">
                                                    {{ $clase->asignacion->seccion->nombre }}
                                                </span>
                                            </td>
                                            <td>{{ $clase->aula ?? 'Aula por asignar' }}</td>
                                            <td>{{ $clase->asignacion->seccion->grado->nombre }}</td>
                                            <td>
                                                @if($asistencia)
                                                    <span class="badge bg-success rounded-pill px-4 py-2">
                                                        <i class="fas fa-check-circle mr-1"></i> Tomada
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning rounded-pill px-4 py-2">
                                                        <i class="fas fa-clock mr-1"></i> Pendiente
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center">
                                                    @if($asistencia)
                                                        <a href="{{ route('asistencias.edit', $asistencia->id) }}" 
                                                           class="btn btn-sm btn-light border mx-1"
                                                           data-toggle="tooltip" 
                                                           title="Editar asistencia">
                                                            <i class="fas fa-edit text-warning"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('asistencias.registrar', [$clase->asignacion->materia->id, $clase->id]) }}" 
                                                           class="btn btn-sm btn-light border mx-1"
                                                           data-toggle="tooltip" 
                                                           title="Registrar asistencia">
                                                            <i class="fas fa-plus text-primary"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                                <p class="mb-0">No hay clases programadas para hoy</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('/build/assets/admin/admin.css') }}">
<style>
    .card-statistic {
        border-left: 4px solid;
        height: 100%;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    
    .card-statistic .card-body {
        padding: 1rem;
    }
    
    .card-icon {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        background: rgba(0,0,0,0.05);
        border-radius: 0.375rem;
    }
    
    .list-group-item {
        transition: all 0.2s ease;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 0.75rem 1rem;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
        border-left: 3px solid #6c757d;
    }
    
    .list-group-item-action {
        color: #495057;
        text-decoration: none;
        display: flex;
        align-items: center;
    }
    
    .list-group-item-action:hover {
        color: #212529;
    }
    
    .list-group-item-action i {
        margin-right: 0.5rem;
        width: 1.5rem;
        text-align: center;
    }
    
    .chart-container {
        background-color: white;
        border-radius: 0.5rem;
        padding: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    
    .chartjs-render-monitor {
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .section-card {
        transition: all 0.2s ease;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    
    .section-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
    }
    
    .table-responsive-md {
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead {
        background-color: #f8f9fa;
    }
    
    .table th {
        font-weight: 600;
        color: #495057;
        padding: 0.75rem;
        border-bottom: 2px solid #dee2e6;
    }
    
    .asistencia-tomada {
        background-color: rgba(40, 167, 69, 0.1); 
        border-left: 4px solid #28a745; 
        transition: all 0.3s ease;
    }
    
    .asistencia-tomada:hover {
        background-color: rgba(40, 167, 69, 0.15);
        border-left: 4px solid #218838;
    }
    
    .table td {
        padding: 0.75rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    @media (max-width: 768px) {
        .chart-container {
            height: auto !important;
        }
        
        .card-body {
            padding: 0.75rem;
        }
        
        .table-responsive-md {
            border-radius: 0.25rem;
        }
    }

    .card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    
    .card-header {
        background-color: white;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .card-title {
        font-weight: 600;
        color: #212529;
    }
    
    .badge {
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
    }    
    
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .card-header {
        padding: 1rem 1.25rem;
        background-color: white;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .card-header .card-title i {
        font-size: 1.2rem;
        margin-right: 8px;
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    const summaryCtx = document.getElementById('attendanceSummaryChart');
    if (summaryCtx) {
        new Chart(summaryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Presentes', 'Tardíos', 'Ausentes'],
                datasets: [{
                    data: [@json($asistenciasHoy), @json($tardiosHoy), @json($inasistenciasHoy)],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.9)',
                        'rgba(255, 193, 7, 0.9)',
                        'rgba(220, 53, 69, 0.9)'
                    ],
                    hoverBackgroundColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 0,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            boxWidth: 12,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const value = context.raw;
                                const percentage = Math.round((value / total) * 100);
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#fff',
                        borderWidth: 1,
                        padding: 12
                    }
                },
                cutout: '70%',
                animation: {
                    animateRotate: true,
                    animateScale: true
                }
            }
        });
    }

    const sectionCtx = document.getElementById('attendanceBySectionChart');
    if (sectionCtx) {
        const secciones = ['Sección A', 'Sección B', 'Sección C'];
        const presentes = [25, 30, 20];
        const tardios = [3, 5, 2];
        const ausentes = [2, 5, 3];

        new Chart(sectionCtx, {
            type: 'bar',
            data: {
                labels: secciones,
                datasets: [
                    {
                        label: 'Asistentes',
                        data: presentes,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)'
                    },
                    {
                        label: 'Pases',
                        data: tardios,
                        backgroundColor: 'rgba(255, 193, 7, 0.7)'
                    },
                    {
                        label: 'Inasistentes',
                        data: ausentes,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Asistencia por Sección'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });
    }
});
</script>
@endsection