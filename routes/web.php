<?php

use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupervisionController;
use App\Http\Controllers\UnidadController;
use App\Http\Controllers\UserController;
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
    Route::delete('/conductores/{conductor}', [ConductorController::class, 'destroy'])->name('conductores.destroy');
    Route::post('/conductores/bulk-delete', [ConductorController::class, 'destroyMultiple'])->name('conductores.destroyMultiple');
    Route::post('/unidades/bulk-delete', [UnidadController::class, 'destroyMultiple'])->name('unidades.destroyMultiple');

    // Supervisión / Faltas
    Route::get('/supervision', [SupervisionController::class, 'index'])->name('supervision.index');
    Route::post('/supervision/faltas', [SupervisionController::class, 'store'])->name('supervision.faltas.store');
    Route::delete('/supervision/faltas/{falta}', [SupervisionController::class, 'destroyFalta'])->name('supervision.faltas.destroy');
    Route::get('/supervision/exportar', [SupervisionController::class, 'exportExcel'])->name('supervision.exportar');
    Route::get('/supervision/imprimir', [SupervisionController::class, 'printReport'])->name('supervision.imprimir');

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

    Route::get('/blacklist', [BlacklistController::class, 'index'])->name('blacklist.index');
});

require __DIR__.'/auth.php';
