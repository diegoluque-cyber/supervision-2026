<?php

use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupervisionController;
use App\Http\Controllers\UnidadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PapeletaController;
use App\Http\Controllers\OperativoController;
use App\Http\Controllers\SupervisorAttendanceController;
use App\Http\Controllers\SancionServicioController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/unidades', [UnidadController::class, 'index'])->name('unidades.index');
    Route::get('/unidades/exportar', [UnidadController::class, 'exportExcel'])->name('unidades.exportar');
    Route::get('/conductores', [ConductorController::class, 'index'])->name('conductores.index');
    Route::get('/conductores/imprimir', [ConductorController::class, 'printReport'])->name('conductores.imprimir');
    Route::post('/conductores', [ConductorController::class, 'store'])->name('conductores.store');
    Route::put('/conductores/{conductor}', [ConductorController::class, 'update'])->name('conductores.update');
    Route::delete('/conductores/{conductor}', [ConductorController::class, 'destroy'])->name('conductores.destroy');
    Route::post('/conductores/bulk-delete', [ConductorController::class, 'destroyMultiple'])->name('conductores.destroyMultiple');
    Route::put('/conductores/{conductor}/quitar-citacion', [ConductorController::class, 'quitarCitacion'])->name('conductores.quitar-citacion');
    Route::post('/unidades/bulk-delete', [UnidadController::class, 'destroyMultiple'])->name('unidades.destroyMultiple');

    // Supervisión / Faltas
    Route::get('/supervision', [SupervisionController::class, 'index'])->name('supervision.index');
    Route::post('/supervision/faltas', [SupervisionController::class, 'store'])->name('supervision.faltas.store');
    Route::post('/supervision/faltas/{falta}/descartar-alarma', [SupervisionController::class, 'descartarAlarma'])->name('supervision.faltas.descartar-alarma');
    Route::delete('/supervision/faltas/{falta}', [SupervisionController::class, 'destroyFalta'])->name('supervision.faltas.destroy');
    Route::get('/supervision/exportar', [SupervisionController::class, 'exportExcel'])->name('supervision.exportar');
    Route::get('/supervision/imprimir', [SupervisionController::class, 'printReport'])->name('supervision.imprimir');

    // Papeletas
    Route::get('/caja', [PapeletaController::class, 'caja'])->name('caja.index');
    Route::post('/papeletas', [PapeletaController::class, 'storeGeneral'])->name('papeletas.storeGeneral');
    Route::post('/unidades/{unidad}/papeletas', [PapeletaController::class, 'store'])->name('unidades.papeletas.store');
    Route::put('/papeletas/{papeleta}/toggle-pago', [PapeletaController::class, 'togglePago'])->name('papeletas.toggle-pago');
    Route::delete('/papeletas/{papeleta}', [PapeletaController::class, 'destroy'])->name('papeletas.destroy');

    // Sanciones de Servicios
    Route::get('/sanciones', [SancionServicioController::class, 'index'])->name('sanciones.index');
    Route::post('/sanciones', [SancionServicioController::class, 'store'])->name('sanciones.store');
    Route::post('/sanciones/clear-history', [SancionServicioController::class, 'clearHistory'])->name('sanciones.clear-history');
    Route::put('/sanciones/{sancion}/status', [SancionServicioController::class, 'updateStatus'])->name('sanciones.update-status');
    Route::put('/sanciones/{sancion}/liberar', [SancionServicioController::class, 'liberar'])->name('sanciones.liberar');
    Route::put('/sanciones/{sancion}/add-service', [SancionServicioController::class, 'addService'])->name('sanciones.add-service');
    Route::put('/sanciones/{sancion}/remove-service', [SancionServicioController::class, 'removeService'])->name('sanciones.remove-service');

    Route::get('/importar', [ImportController::class, 'index'])->name('importar.index');
    Route::post('/importar/preview', [ImportController::class, 'preview'])->name('importar.preview');
    Route::post('/importar', [ImportController::class, 'store'])->name('importar.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/supervisores', [UserController::class, 'index'])->name('supervisores.index');
    Route::post('/supervisores', [UserController::class, 'store'])->name('supervisores.store');
    Route::put('/supervisores/{user}', [UserController::class, 'update'])->name('supervisores.update');
    Route::delete('/supervisores/{user}', [UserController::class, 'destroy'])->name('supervisores.destroy');
    Route::post('/supervisores/bulk-delete', [UserController::class, 'destroyMultiple'])->name('supervisores.destroyMultiple');

    // Asistencia de Supervisores (Miércoles)
    Route::get('/supervisores/asistencia', [SupervisorAttendanceController::class, 'index'])->name('supervisores.asistencia.index');
    Route::post('/supervisores/asistencia', [SupervisorAttendanceController::class, 'storeMeeting'])->name('supervisores.asistencia.store');
    Route::get('/supervisores/asistencia/{meeting}', [SupervisorAttendanceController::class, 'showMeeting'])->name('supervisores.asistencia.show');
    Route::post('/supervisores/asistencia/{meeting}/guardar', [SupervisorAttendanceController::class, 'saveAttendance'])->name('supervisores.asistencia.save');
    Route::delete('/supervisores/asistencia/{meeting}', [SupervisorAttendanceController::class, 'destroyMeeting'])->name('supervisores.asistencia.destroy');

    Route::get('/blacklist', [BlacklistController::class, 'index'])->name('blacklist.index');

    // Operativos
    Route::get('/operativos', [OperativoController::class, 'index'])->name('operativos.index');
    Route::post('/operativos', [OperativoController::class, 'store'])->name('operativos.store');
    Route::get('/operativos/{operativo}', [OperativoController::class, 'show'])->name('operativos.show');
    Route::put('/operativos/{operativo}', [OperativoController::class, 'update'])->name('operativos.update');
    Route::delete('/operativos/{operativo}', [OperativoController::class, 'destroy'])->name('operativos.destroy');
    Route::post('/operativos/{operativo}/faltas', [OperativoController::class, 'storeFalta'])->name('operativos.faltas.store');
    Route::post('/operativos/{operativo}/papeletas', [OperativoController::class, 'storePapeleta'])->name('operativos.papeletas.store');
    Route::put('/operativos/faltas/{falta}/unlink', [OperativoController::class, 'unlinkFalta'])->name('operativos.faltas.unlink');
    Route::put('/operativos/papeletas/{papeleta}/unlink', [OperativoController::class, 'unlinkPapeleta'])->name('operativos.papeletas.unlink');
    Route::delete('/operativos/faltas/{falta}', [OperativoController::class, 'destroyFalta'])->name('operativos.faltas.destroy');
    Route::delete('/operativos/papeletas/{papeleta}', [OperativoController::class, 'destroyPapeleta'])->name('operativos.papeletas.destroy');

    // Intervenciones en Operativos
    Route::post('/operativos/{operativo}/intervenciones', [OperativoController::class, 'storeIntervencion'])->name('operativos.intervenciones.store');
    Route::put('/operativos/intervenciones/{intervencion}', [OperativoController::class, 'updateIntervencion'])->name('operativos.intervenciones.update');
    Route::delete('/operativos/intervenciones/{intervencion}', [OperativoController::class, 'destroyIntervencion'])->name('operativos.intervenciones.destroy');
});

require __DIR__.'/auth.php';
