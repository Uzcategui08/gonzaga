<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\ProfesorController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\GradoMateriaController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\JustificativoController;
use App\Http\Controllers\AsistenciaReporteController;
use App\Http\Controllers\AsistenciaSecretariaController;
use App\Http\Controllers\PaseController;
use App\Http\Controllers\LimpiezaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AsistenciaMensualController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/horarios', [HorarioController::class, 'index'])->name('horarios.index');
Route::get('/horarios/create', [HorarioController::class, 'create'])->name('horarios.create');
Route::post('/horarios', [HorarioController::class, 'store'])->name('horarios.store');
Route::get('/horarios/{horario}/edit', [HorarioController::class, 'edit'])->name('horarios.edit');
Route::put('/horarios/{horario}', [HorarioController::class, 'update'])->name('horarios.update');
Route::delete('/horarios/{horario}', [HorarioController::class, 'destroy'])->name('horarios.destroy');

Route::get('/horario-profesor', [HorarioController::class, 'horarioProfesor'])->name('horario.profesor');

Route::get('/asistencias/reporte', [AsistenciaController::class, 'reporte'])->name('asistencias.reporte');
require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::post('/notifications/{notification}/mark-as-read', ['App\Http\Controllers\NotificationController', 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', ['App\Http\Controllers\NotificationController', 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::get('/estudiantes/{estudiante}/horarios', [EstudianteController::class, 'getHorarios'])->name('estudiantes.horarios');
    Route::get('/notifications/update', ['App\Http\Controllers\NotificationController', 'update'])->name('notifications.update');
});

Route::get('/horarios/profesor/admin', [HorarioController::class, 'horarioProfesorAdmin'])
    ->name('horarios.profesor.admin')
    ->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::resource('grados', GradoController::class)->names([
        'index' => 'grados.index',
        'create' => 'grados.create',
        'store' => 'grados.store',
        'show' => 'grados.show',
        'edit' => 'grados.edit',
        'update' => 'grados.update',
        'destroy' => 'grados.destroy'
    ]);

    Route::get('/grados/data', [GradoController::class, 'getData'])
        ->name('grados.data')
        ->middleware('auth');

    Route::resource('secciones', SeccionController::class)->parameters(['secciones' => 'seccion']);
    Route::resource('estudiantes', EstudianteController::class);
    Route::resource('materias', MateriaController::class);
    Route::resource('profesores', ProfesorController::class)->parameters(['profesores' => 'profesor']);

    Route::get('limpiezas/asignar/{clase?}', [LimpiezaController::class, 'create'])->middleware(['auth', \App\Http\Middleware\CheckUserType::class . ':profesor,coordinador,admin'])->name('limpiezas.create');

    Route::resource('limpiezas', LimpiezaController::class, ['except' => ['create']])->middleware(['auth', \App\Http\Middleware\CheckUserType::class . ':profesor,coordinador,admin']);
    Route::get('api/clase/{clase}/estudiantes', [LimpiezaController::class, 'getEstudiantes'])->name('api.clase.estudiantes');

    Route::resource('asignaciones', AsignacionController::class)->parameters(['asignaciones' => 'asignacion']);
    Route::get('asignaciones/estudiantes/por-seccion', [AsignacionController::class, 'getEstudiantesBySeccion'])->name('asignaciones.estudiantes.por-seccion');

    Route::get('/asistencias/reporte', [AsistenciaReporteController::class, 'index'])
        ->middleware([\App\Http\Middleware\CheckUserType::class . ':admin,profesor,coordinador,secretaria'])
        ->name('asistencias.reporte');
    Route::get('/asistencias/reporte-pdf', [AsistenciaReporteController::class, 'generatePdf'])->name('asistencias.reporte-pdf');
    Route::get('/asistencias/inasistencias-coordinador', [\App\Http\Controllers\AsistenciaCoordinadorController::class, 'index'])
        ->name('asistencias.coordinador.index');
    Route::get('/asistencias/inasistencias-coordinador/pdf', [\App\Http\Controllers\AsistenciaCoordinadorController::class, 'exportPdf'])
        ->name('asistencias.coordinador.pdf');
    Route::get('/asistencias/reporte-secretaria', [AsistenciaSecretariaController::class, 'index'])
        ->middleware([\App\Http\Middleware\CheckUserType::class . ':secretaria,admin'])
        ->name('asistencias.secretaria.index');
    Route::get('/asistencias/reporte-secretaria/pdf', [AsistenciaSecretariaController::class, 'exportPdf'])
        ->middleware([\App\Http\Middleware\CheckUserType::class . ':secretaria,admin'])
        ->name('asistencias.secretaria.pdf');
    Route::get('/asistencias/reporte-secretaria/excel', [AsistenciaSecretariaController::class, 'exportExcel'])
        ->middleware([\App\Http\Middleware\CheckUserType::class . ':secretaria,admin'])
        ->name('asistencias.secretaria.excel');
    Route::get('materias/{materia}/asistencia', [AsistenciaController::class, 'index'])->name('asistencias.index');
    Route::post('materias/{materia}/asistencia', [AsistenciaController::class, 'store'])->name('asistencias.store');
    Route::get('asistencias/{asistencia}/pdf', [AsistenciaController::class, 'generatePdf'])->name('asistencias.generate-pdf');
    Route::get('/asistencias/create/{materiaId}/{horarioId}', [AsistenciaController::class, 'create'])->name('asistencias.create');
    Route::resource('asistencias', AsistenciaController::class);
    Route::get('/asistencia/notas-clase', [AsistenciaController::class, 'notasClase'])->name('asistencia.notas-clase');
    Route::get('/asistencia/notas-clase/pdf/{asistencia}', [AsistenciaController::class, 'notasClasePdfIndividual'])->name('asistencia.notas-clase.pdf-individual');
    Route::get('/asistencia/mensual', [AsistenciaMensualController::class, 'index'])->name('asistencia.mensual.index');
    Route::get('/asistencia/mensual/pdf', [AsistenciaMensualController::class, 'generatePdf'])->name('asistencia.mensual.pdf');

    Route::resource('pases', PaseController::class);
    Route::get('asistencias/registrar/{materia}/{horario}', [AsistenciaController::class, 'registrar'])->name('asistencias.registrar');
    Route::get('asistencias/{asistencia}/edit', [AsistenciaController::class, 'edit'])->name('asistencias.edit')->middleware('auth');
    Route::resource('horarios', HorarioController::class);
    Route::resource('grado-materia', GradoMateriaController::class);

    Route::get('justificativos/profesor', [JustificativoController::class, 'indexProfesor'])->middleware(['auth', \App\Http\Middleware\CheckUserType::class . ':profesor'])->name('justificativos.profesor');

    Route::get('justificativos/profesor/{justificativo}', [JustificativoController::class, 'show'])->middleware(['auth'])->name('justificativos.profesor.show');

    Route::get('justificativos/admin', [JustificativoController::class, 'index'])->middleware(['auth', \App\Http\Middleware\CheckUserType::class . ':coordinador,admin'])->name('justificativos.admin');

    Route::prefix('justificativos')->middleware(['auth', \App\Http\Middleware\CheckUserType::class . ':coordinador,admin'])->group(function () {
        Route::get('/', [JustificativoController::class, 'index'])->name('justificativos.index');
        Route::get('nuevo', [JustificativoController::class, 'create'])->name('justificativos.create');
        Route::get('nuevo/{estudiante}', [JustificativoController::class, 'createSpecific'])->name('justificativos .create-specific');
        Route::post('/', [JustificativoController::class, 'store'])->name('justificativos.store');
        Route::get('{justificativo}', [JustificativoController::class, 'show'])->name('justificativos.admin.show');
        Route::get('show/{justificativo}', [JustificativoController::class, 'show'])->name('justificativos.show');
        Route::get('{justificativo}/edit', [JustificativoController::class, 'edit'])->name('justificativos.edit');
        Route::put('{justificativo}', [JustificativoController::class, 'update'])->name('justificativos.update');
        Route::put('{justificativo}/approve', [JustificativoController::class, 'approve'])->name('justificativos.approve');
        Route::delete('{justificativo}', [JustificativoController::class, 'destroy'])->name('justificativos.destroy');
    });
});
