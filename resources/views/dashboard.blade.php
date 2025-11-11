@extends('adminlte::page')

@section('title', 'Inicio')

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

	$fechaHeader = now('America/Caracas');
	$diaHeader = $diasHeader[$fechaHeader->format('l')];
	$mesHeader = $mesesHeader[$fechaHeader->format('F')];
@endphp
<div class="d-flex justify-content-between align-items-center">
	<h2 class="mb-0">Dashboard</h2>
	<span class="text-muted">{{ $fechaHeader->format('d') . ' de ' . $mesHeader . ' de ' . $fechaHeader->format('Y') }}</span>
</div>
<hr class="mt-2 mb-4">
@endsection

@section('content')
@php
	$usuario = auth()->user();
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

	$fechaActual = now('America/Caracas');
	$diaActual = $dias[$fechaActual->format('l')];
	$mesActual = $meses[$fechaActual->format('F')];
	$fechaLarga = $diaActual . ', ' . $fechaActual->format('d') . ' de ' . $mesActual . ' de ' . $fechaActual->format('Y');

	$tituloUsuario = $usuario->name;
	if ($usuario->hasRole('profesor')) {
		$tituloUsuario = 'Profesor ' . $usuario->name;
	} elseif ($usuario->hasRole('admin')) {
		$tituloUsuario = 'Administrador ' . $usuario->name;
	} elseif ($usuario->hasRole('coordinador')) {
		$tituloUsuario = 'Coordinador ' . $usuario->name;
	}

	$totalClasesDia = $totalClases ?? 0;
	$clasesRegistradas = $clasesConAsistencia ?? 0;
	$clasesPendientes = max($totalClasesDia - $clasesRegistradas, 0);
	$porcentajeRegistrado = $totalClasesDia ? round(($clasesRegistradas / max($totalClasesDia, 1)) * 100) : 0;
	$porcentajePendiente = $totalClasesDia ? round(($clasesPendientes / max($totalClasesDia, 1)) * 100) : 0;
	$totalEstudiantesProfesor = $totalEstudiantesProfesor ?? 0;
	$inasistenciasProfesor = $inasistenciasProfesor ?? 0;
	$porcentajeInasistencias = $totalEstudiantesProfesor ? round(min(100, ($inasistenciasProfesor / max($totalEstudiantesProfesor, 1)) * 100)) : 0;

	$asistenciasContadas = is_numeric($asistenciasHoy ?? null) ? (int) $asistenciasHoy : 0;
	$tardiosContados = is_numeric($tardiosHoy ?? null) ? (int) $tardiosHoy : 0;
	$inasistenciasContadas = is_numeric($inasistenciasHoy ?? null) ? (int) $inasistenciasHoy : 0;
	$totalProcesadas = max($asistenciasContadas + $tardiosContados + $inasistenciasContadas, 1);
	$asistenciasPct = round(($asistenciasContadas / $totalProcesadas) * 100, 2);
	$tardiosPct = round(($tardiosContados / $totalProcesadas) * 100, 2);
	$inasistenciasPct = round(($inasistenciasContadas / $totalProcesadas) * 100, 2);

	$asistenciasHoyCollection = ($usuario->hasRole('profesor') && isset($asistenciasHoy) && $asistenciasHoy instanceof \Illuminate\Support\Collection)
		? $asistenciasHoy
		: collect();
	$fechaHoy = now('America/Caracas');
@endphp

<div class="container-fluid">
@if(!$usuario->hasRole('profesor'))
	<div class="row justify-content-center">
		<div class="col-lg-7 col-md-9">
			<div class="card shadow-lg border-0 maintenance-card">
				<div class="card-body text-center p-4 p-md-5">
					<div class="maintenance-icon mb-4">
						<i class="fas fa-tools"></i>
					</div>
					<h3 class="font-weight-bold mb-3">Dashboard en mantenimiento</h3>
					<p class="text-muted mb-4">
						Estamos renovando esta sección para ofrecerte una experiencia más clara y útil. Mientras tanto, todas las demás áreas del sistema siguen disponibles con normalidad.
					</p>
					<a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="btn btn-outline-primary btn-sm px-4">
						<i class="fas fa-arrow-left mr-2"></i>Volver a navegar
					</a>
					<a href="{{ route('asistencias.reporte') }}" class="btn btn-primary btn-sm px-4 ml-2">
						<i class="fas fa-external-link-alt mr-2"></i>Ir al módulo de asistencias
					</a>
				</div>
			</div>
		</div>
	</div>
@else
	<div class="row mb-4">
		<div class="col-12">
			<div class="hero-card">
				<div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between w-100">
					<div>
						<p class="hero-subtitle mb-1">Bienvenido de vuelta</p>
						<h3 class="hero-title mb-3">{{ $tituloUsuario }}</h3>
						<p class="hero-date mb-4 mb-lg-3">{{ $fechaLarga }}</p>
						<div class="hero-metrics">
							@if($usuario->hasRole('profesor'))
								<span class="hero-pill">
									<i class="fas fa-calendar-day"></i>
									{{ $totalClasesDia }} clases hoy
								</span>
								<span class="hero-pill hero-pill--success">
									<i class="fas fa-clipboard-check"></i>
									{{ $clasesRegistradas }} registradas
								</span>
								<span class="hero-pill hero-pill--warning">
									<i class="fas fa-clock"></i>
									{{ $clasesPendientes }} pendientes
								</span>
							@else
								<span class="hero-pill">
									<i class="fas fa-check-circle"></i>
									{{ $asistenciasContadas }} asistencias
								</span>
								<span class="hero-pill hero-pill--warning">
									<i class="fas fa-clock"></i>
									{{ $tardiosContados }} pases
								</span>
								<span class="hero-pill hero-pill--danger">
									<i class="fas fa-times-circle"></i>
									{{ $inasistenciasContadas }} inasistencias
								</span>
							@endif
						</div>
					</div>
					<div class="hero-illustration mt-4 mt-lg-0">
						<div class="hero-graphic">
							<i class="fas fa-chart-line"></i>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	@if($usuario->hasRole('profesor'))
		<div class="row g-3 mb-4">
			<div class="col-xl-3 col-md-6">
				<div class="stat-card stat-card--primary">
					<div class="stat-card__icon">
						<i class="fas fa-calendar-alt"></i>
					</div>
					<div>
						<span class="stat-card__label">Clases programadas</span>
						<span class="stat-card__value">{{ $totalClasesDia }}</span>
						<span class="stat-card__meta">Total día actual</span>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6">
				<div class="stat-card stat-card--success">
					<div class="stat-card__icon">
						<i class="fas fa-check-circle"></i>
					</div>
					<div class="w-100">
						<span class="stat-card__label">Clases con asistencia</span>
						<span class="stat-card__value">{{ $clasesRegistradas }}</span>
						<div class="stat-card__progress">
							<div class="stat-card__progress-bar" style="width: {{ $porcentajeRegistrado }}%"></div>
						</div>
						<span class="stat-card__meta">{{ $porcentajeRegistrado }}% completado</span>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6">
				<div class="stat-card stat-card--warning">
					<div class="stat-card__icon">
						<i class="fas fa-bell"></i>
					</div>
					<div class="w-100">
						<span class="stat-card__label">Clases pendientes</span>
						<span class="stat-card__value">{{ $clasesPendientes }}</span>
						<div class="stat-card__progress">
							<div class="stat-card__progress-bar" style="width: {{ $porcentajePendiente }}%"></div>
						</div>
						<span class="stat-card__meta">Por registrar</span>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6">
				<div class="stat-card stat-card--danger">
					<div class="stat-card__icon">
						<i class="fas fa-user-times"></i>
					</div>
					<div class="w-100">
						<span class="stat-card__label">Inasistencias estudiantes</span>
						<span class="stat-card__value">{{ $inasistenciasProfesor }}</span>
						<div class="stat-card__progress">
							<div class="stat-card__progress-bar" style="width: {{ $porcentajeInasistencias }}%"></div>
						</div>
						<span class="stat-card__meta">{{ $porcentajeInasistencias }}% del grupo</span>
					</div>
				</div>
			</div>
		</div>
	@else
		<div class="row g-3 mb-4">
			<div class="col-xl-3 col-md-6">
				<div class="stat-card stat-card--primary">
					<div class="stat-card__icon">
						<i class="fas fa-user-graduate"></i>
					</div>
					<div>
						<span class="stat-card__label">Estudiantes totales</span>
						<span class="stat-card__value">{{ $totalEstudiantes }}</span>
						<span class="stat-card__meta">Usuarios activos</span>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6">
				<div class="stat-card stat-card--indigo">
					<div class="stat-card__icon">
						<i class="fas fa-chalkboard-teacher"></i>
					</div>
					<div>
						<span class="stat-card__label">Profesores totales</span>
						<span class="stat-card__value">{{ $totalProfesores ?? 0 }}</span>
						<span class="stat-card__meta">Equipo docente</span>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6">
				<div class="stat-card stat-card--teal">
					<div class="stat-card__icon">
						<i class="fas fa-book-open"></i>
					</div>
					<div>
						<span class="stat-card__label">Clases hoy</span>
						<span class="stat-card__value">{{ $totalClasesHoy ?? 0 }}</span>
						<span class="stat-card__meta">Registradas en el sistema</span>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6">
				<div class="stat-card stat-card--success">
					<div class="stat-card__icon">
						<i class="fas fa-percent"></i>
					</div>
					<div>
						<span class="stat-card__label">Promedio asistencia</span>
						<span class="stat-card__value">{{ isset($promedioAsistencia) ? $promedioAsistencia : 0 }}%</span>
						<span class="stat-card__meta">Últimos 30 días</span>
					</div>
				</div>
			</div>
		</div>

		<div class="row g-3 mb-4">
			<div class="col-xl-3 col-md-6">
				<div class="stat-card stat-card--emerald">
					<div class="stat-card__icon">
						<i class="fas fa-user-check"></i>
					</div>
					<div>
						<span class="stat-card__label">Asistencias hoy</span>
						<span class="stat-card__value">{{ $asistenciasContadas }}</span>
						<span class="stat-card__meta">Incluye pases</span>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6">
				<div class="stat-card stat-card--warning">
					<div class="stat-card__icon">
						<i class="fas fa-clock"></i>
					</div>
					<div>
						<span class="stat-card__label">Pases registrados</span>
						<span class="stat-card__value">{{ $tardiosContados }}</span>
						<span class="stat-card__meta">Reportados hoy</span>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6">
				<div class="stat-card stat-card--danger">
					<div class="stat-card__icon">
						<i class="fas fa-user-times"></i>
					</div>
					<div>
						<span class="stat-card__label">Inasistencias hoy</span>
						<span class="stat-card__value">{{ $inasistenciasContadas }}</span>
						<span class="stat-card__meta">Acumuladas en el día</span>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6">
				<div class="stat-card stat-card--purple">
					<div class="stat-card__icon">
						<i class="fas fa-star"></i>
					</div>
					<div>
						<span class="stat-card__label">Clase destacada</span>
						<span class="stat-card__value">{{ $claseTop ?? 'N/A' }}</span>
						<span class="stat-card__meta">{{ $asistenciaClaseTop ?? 0 }} asistencias</span>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6">
				<div class="stat-card stat-card--info">
					<div class="stat-card__icon">
						<i class="fas fa-book"></i>
					</div>
					<div>
						<span class="stat-card__label">Materia destacada</span>
						<span class="stat-card__value">{{ $materiaTop ?? 'N/A' }}</span>
						<span class="stat-card__meta">{{ $asistenciaMateriaTop ?? 0 }} asistencias</span>
					</div>
				</div>
			</div>
		</div>
	@endif

	@if(!$usuario->hasRole('profesor'))
		<div class="row g-4 mb-4">
			<div class="col-lg-6">
				<div class="card chart-card h-100">
					<div class="card-header">
						<h3 class="card-title mb-0"><i class="fas fa-chart-bar mr-2 text-primary"></i>Asistencia por día (últimos 30 días)</h3>
					</div>
					<div class="card-body">
						<canvas id="attendanceByDayChart" class="chart-canvas"></canvas>
					</div>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="card chart-card h-100">
					<div class="card-header">
						<h3 class="card-title mb-0"><i class="fas fa-clipboard-check mr-2 text-primary"></i>Resumen de asistencia de hoy</h3>
					</div>
					<div class="card-body">
						@if(($usuario->hasRole('coordinador') || $usuario->hasRole('admin')) && ($asistenciasContadas > 0 || $tardiosContados > 0 || $inasistenciasContadas > 0))
							<div class="d-flex justify-content-center mb-4">
								<div class="summary-chart">
									<canvas id="attendanceSummaryChart"></canvas>
								</div>
							</div>
							<div class="row text-center mb-3">
								<div class="col-4">
									<div class="summary-chip summary-chip--success">
										<i class="fas fa-check-circle"></i>
										<span class="summary-chip__value">{{ $asistenciasContadas }}</span>
										<span class="summary-chip__label">Asistentes</span>
									</div>
								</div>
								<div class="col-4">
									<div class="summary-chip summary-chip--warning">
										<i class="fas fa-clock"></i>
										<span class="summary-chip__value">{{ $tardiosContados }}</span>
										<span class="summary-chip__label">Pases</span>
									</div>
								</div>
								<div class="col-4">
									<div class="summary-chip summary-chip--danger">
										<i class="fas fa-times-circle"></i>
										<span class="summary-chip__value">{{ $inasistenciasContadas }}</span>
										<span class="summary-chip__label">Inasistentes</span>
									</div>
								</div>
							</div>
							<div class="progress progress-slim">
								<div class="progress-bar bg-success" role="progressbar" style="width: {{ $asistenciasPct }}%"></div>
								<div class="progress-bar bg-warning" role="progressbar" style="width: {{ $tardiosPct }}%"></div>
								<div class="progress-bar bg-danger" role="progressbar" style="width: {{ $inasistenciasPct }}%"></div>
							</div>
						@else
							<div class="text-center py-4 text-muted">
								<i class="fas fa-info-circle fa-2x mb-3"></i>
								<p class="mb-0">No hay registros de asistencia para hoy</p>
							</div>
						@endif
					</div>
				</div>
			</div>
		</div>
	@endif

	@if($usuario->hasRole('profesor') && isset($horarioHoy))
		<div class="row mb-4">
			<div class="col-12">
				<div class="card table-card">
					<div class="card-header d-flex align-items-center justify-content-between flex-wrap">
						<h3 class="card-title mb-0 d-flex align-items-center">
							<i class="fas fa-calendar-day text-primary mr-2"></i>
							Horario de hoy
						</h3>
					</div>
					<div class="card-body p-0">
						<div class="table-responsive">
							<table id="horarioHoyTable" class="table table-modern mb-0">
								<thead>
									<tr>
										<th>#</th>
										<th>Hora</th>
										<th>Asignatura</th>
										<th>Sección</th>
										<th>Aula</th>
										<th>Año</th>
										<th>Estado</th>
										<th class="text-center">Acciones</th>
									</tr>
								</thead>
								<tbody>
									@forelse($horarioHoy as $index => $clase)
										@php
											$asistencia = $asistenciasHoyCollection->first(function ($registro) use ($clase, $fechaHoy) {
												return $registro->fecha->toDateString() === $fechaHoy->toDateString() && $registro->horario_id === $clase->id;
											});
										@endphp
										<tr class="{{ $asistencia ? 'asistencia-tomada' : '' }}">
											<td>{{ $loop->iteration }}</td>
											<td>
												<span class="badge badge-primary">{{ $clase->hora_inicio }} - {{ $clase->hora_fin }}</span>
											</td>
											<td>{{ $clase->asignacion->materia->nombre }}</td>
											<td>
												<span class="badge badge-primary">{{ $clase->asignacion->seccion->nombre }}</span>
											</td>
											<td>{{ $clase->aula ?? 'Aula por asignar' }}</td>
											<td>{{ $clase->asignacion->seccion->grado->nombre }}</td>
											<td>
												@if($asistencia)
													<span class="status-chip status-chip--success">
														<i class="fas fa-check"></i> Tomada
													</span>
												@else
													<span class="status-chip status-chip--warning">
														<i class="fas fa-clock"></i> Pendiente
													</span>
												@endif
											</td>
											<td class="text-center">
												<div class="action-buttons">
													@if($asistencia)
														<a href="{{ route('asistencias.edit', $asistencia->id) }}" class="btn btn-icon btn-light" data-toggle="tooltip" title="Editar asistencia">
															<i class="fas fa-edit text-warning"></i>
														</a>
													@else
														<a href="{{ route('asistencias.registrar', [$clase->asignacion->materia->id, $clase->id]) }}" class="btn btn-icon btn-light" data-toggle="tooltip" title="Registrar asistencia">
															<i class="fas fa-plus text-primary"></i>
														</a>
													@endif
												</div>
											</td>
										</tr>
									@empty
										<tr>
											<td colspan="8" class="text-center py-4 text-muted">
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
@endif
</div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('/build/assets/admin/admin.css') }}">
<style>
	.maintenance-card {
		border-radius: 1.5rem;
		background: linear-gradient(145deg, #ffffff 0%, #f5f7ff 100%);
	}

	.maintenance-icon {
		font-size: 3rem;
		color: #6366f1;
		background: rgba(99, 102, 241, 0.12);
		width: 80px;
		height: 80px;
		border-radius: 50%;
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	.hero-card {
		position: relative;
		border-radius: 22px;
		padding: 2.5rem;
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

	.hero-date {
		font-size: 0.95rem;
		opacity: 0.85;
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

	.hero-illustration {
		min-width: 160px;
	}

	.hero-graphic {
		width: 140px;
		height: 140px;
		border-radius: 50%;
		background: rgba(255, 255, 255, 0.12);
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 3rem;
	}

	.stat-card {
		display: flex;
		align-items: flex-start;
		gap: 1rem;
		padding: 1.5rem;
		background: #fff;
		border-radius: 18px;
		box-shadow: 0 18px 35px -25px rgba(15, 23, 42, 0.45);
		transition: transform 0.2s ease, box-shadow 0.2s ease;
		height: 100%;
	}

	.stat-card:hover {
		transform: translateY(-4px);
		box-shadow: 0 22px 45px -28px rgba(15, 23, 42, 0.55);
	}

	.stat-card__icon {
		width: 52px;
		height: 52px;
		border-radius: 15px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 1.3rem;
		color: #fff;
	}

	.stat-card__label {
		font-size: 0.87rem;
		text-transform: uppercase;
		letter-spacing: 0.08rem;
		color: #6B7280;
	}

	.stat-card__value {
		font-size: 1.8rem;
		font-weight: 700;
		color: #111827;
		display: block;
	}

	.stat-card__meta {
		font-size: 0.85rem;
		color: #9CA3AF;
	}

	.stat-card__progress {
		margin: 0.5rem 0;
		width: 100%;
		height: 6px;
		background: #E5E7EB;
		border-radius: 999px;
		overflow: hidden;
	}

	.stat-card__progress-bar {
		height: 100%;
		background: linear-gradient(90deg, #4F46E5, #6366F1);
	}

	.stat-card--primary .stat-card__icon { background: linear-gradient(120deg, #4F46E5, #5B21B6); }
	.stat-card--success .stat-card__icon { background: linear-gradient(120deg, #059669, #10B981); }
	.stat-card--warning .stat-card__icon { background: linear-gradient(120deg, #D97706, #FBBF24); }
	.stat-card--danger .stat-card__icon { background: linear-gradient(120deg, #B91C1C, #EF4444); }
	.stat-card--indigo .stat-card__icon { background: linear-gradient(120deg, #4338CA, #6366F1); }
	.stat-card--teal .stat-card__icon { background: linear-gradient(120deg, #0E7490, #14B8A6); }
	.stat-card--emerald .stat-card__icon { background: linear-gradient(120deg, #047857, #34D399); }
	.stat-card--purple .stat-card__icon { background: linear-gradient(120deg, #6D28D9, #8B5CF6); }
	.stat-card--info .stat-card__icon { background: linear-gradient(120deg, #0369A1, #0EA5E9); }

	.chart-card {
		border-radius: 18px;
		box-shadow: 0 20px 48px -32px rgba(15, 23, 42, 0.45);
	}

	.chart-card .card-header {
		background: transparent;
		border-bottom: 0;
		padding: 1.25rem 1.5rem;
	}

	.chart-card .card-title {
		font-size: 1.05rem;
		font-weight: 600;
		color: #111827;
	}

	.chart-canvas {
		width: 100% !important;
		height: 320px !important;
	}

	.summary-chart {
		width: 220px;
		height: 220px;
		position: relative;
	}

	.summary-chip {
		background: #F3F4F6;
		border-radius: 12px;
		padding: 1rem 0.75rem;
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 0.35rem;
		font-size: 0.85rem;
	}

	.summary-chip i {
		font-size: 1.4rem;
	}

	.summary-chip__value {
		font-size: 1.3rem;
		font-weight: 700;
	}

	.summary-chip__label {
		text-transform: uppercase;
		letter-spacing: 0.08rem;
		color: #6B7280;
	}

	.summary-chip--success { color: #047857; }
	.summary-chip--warning { color: #B45309; }
	.summary-chip--danger { color: #B91C1C; }

	.progress-slim {
		height: 8px;
		background: #E5E7EB;
		border-radius: 999px;
		overflow: hidden;
	}

	.table-card {
		border-radius: 18px;
		box-shadow: 0 18px 40px -30px rgba(15, 23, 42, 0.55);
	}

	.table-modern thead th {
		border-top: none;
		border-bottom: 1px solid #E5E7EB;
		text-transform: uppercase;
		font-size: 0.75rem;
		letter-spacing: 0.08rem;
		color: #6B7280;
	}

	.table-modern tbody td {
		vertical-align: middle;
		font-size: 0.95rem;
	}

	.badge-soft {
		background: #EEF2FF;
		color: #4F46E5;
		border-radius: 999px;
		padding: 0.35rem 0.75rem;
		font-weight: 500;
	}

	.status-chip {
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
		padding: 0.45rem 0.75rem;
		border-radius: 999px;
		font-size: 0.85rem;
		font-weight: 600;
	}

	.status-chip--success {
		background: rgba(16, 185, 129, 0.15);
		color: #047857;
	}

	.status-chip--warning {
		background: rgba(251, 191, 36, 0.20);
		color: #92400E;
	}

	.action-buttons .btn-icon {
		border-radius: 12px;
		box-shadow: inset 0 0 0 1px #E5E7EB;
		padding: 0.4rem 0.6rem;
		transition: transform 0.15s ease;
	}

	.action-buttons .btn-icon:hover {
		transform: translateY(-2px);
	}

	@media (max-width: 992px) {
		.hero-card {
			padding: 2rem;
		}

		.hero-title {
			font-size: 1.7rem;
		}

		.chart-canvas {
			height: 260px !important;
		}
	}

	@media (max-width: 576px) {
		.hero-metrics {
			flex-direction: column;
			align-items: flex-start;
		}

		.hero-graphic {
			width: 100px;
			height: 100px;
			font-size: 2.2rem;
		}

		.stat-card {
			flex-direction: column;
			align-items: flex-start;
		}

		.summary-chart {
			width: 180px;
			height: 180px;
		}
	}
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		const attendanceByDayElement = document.getElementById('attendanceByDayChart');
		const attendanceSummaryElement = document.getElementById('attendanceSummaryChart');

		const attendanceByDayData = @json(isset($attendanceByDay) ? $attendanceByDay : collect());
		if (attendanceByDayElement && Array.isArray(attendanceByDayData) && attendanceByDayData.length) {
			const dayLabels = attendanceByDayData.map(item => item.dia);
			const dayRates = attendanceByDayData.map(item => item.tasa);

			new Chart(attendanceByDayElement, {
				type: 'bar',
				data: {
					labels: dayLabels,
					datasets: [{
						label: 'Tasa de asistencia %',
						data: dayRates,
						backgroundColor: dayRates.map(() => 'rgba(99, 102, 241, 0.55)'),
						borderRadius: 12,
						maxBarThickness: 38
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					scales: {
						y: {
							beginAtZero: true,
							max: 100,
							ticks: {
								callback: value => value + '%'
							},
							grid: { color: 'rgba(156, 163, 175, 0.15)' }
						},
						x: {
							grid: { display: false }
						}
					},
					plugins: {
						legend: { display: false }
					}
				}
			});
		}

		if (attendanceSummaryElement) {
			const asistencias = {{ $asistenciasContadas }};
			const tardios = {{ $tardiosContados }};
			const inasistencias = {{ $inasistenciasContadas }};

			if (asistencias + tardios + inasistencias > 0) {
				new Chart(attendanceSummaryElement, {
					type: 'doughnut',
					data: {
						labels: ['Asistencias', 'Pases', 'Inasistencias'],
						datasets: [{
							data: [asistencias, tardios, inasistencias],
							backgroundColor: [
								'rgba(16, 185, 129, 0.75)',
								'rgba(245, 158, 11, 0.75)',
								'rgba(239, 68, 68, 0.75)'
							],
							borderWidth: 0,
							hoverOffset: 6
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						cutout: '68%',
						plugins: {
							legend: {
								display: true,
								position: 'bottom',
								labels: {
									usePointStyle: true,
									padding: 20
								}
							}
						}
					}
				});
			}
		}

		if (window.jQuery && $('#horarioHoyTable').length) {
			$('#horarioHoyTable').DataTable({
				language: {
					url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
				},
				responsive: true,
				pageLength: 5,
				lengthMenu: [5, 10, 20],
				order: [[1, 'asc']]
			});
		}

		$('[data-toggle="tooltip"]').tooltip();
	});
</script>
@endsection
