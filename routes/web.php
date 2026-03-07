<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ServiceTableController;
use App\Http\Controllers\ShopSettingsController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\StageController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Página pública
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Configuración del Local
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/settings', [ShopSettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [ShopSettingsController::class, 'update'])->name('settings.update');

});

/*
|--------------------------------------------------------------------------
| Gestión de Canciones
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/songs', [SongController::class, 'index'])->name('songs.index');
    Route::post('/songs', [SongController::class, 'store'])->name('songs.store');
    Route::delete('/songs/{song}', [SongController::class, 'destroy'])->name('songs.destroy');

});

/*
|--------------------------------------------------------------------------
| Gestión de Mesas
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/tables', [ServiceTableController::class, 'index'])->name('tables.index');
    Route::post('/tables', [ServiceTableController::class, 'store'])->name('tables.store');
    Route::put('/tables/{table}', [ServiceTableController::class, 'update'])->name('tables.update');
    Route::delete('/tables/{table}', [ServiceTableController::class, 'destroy'])->name('tables.destroy');

});

/*
|--------------------------------------------------------------------------
| Gestión de Cola de Karaoke
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/queues', [QueueController::class, 'index'])->name('queues.index');

    Route::patch('/queues/{queue}/update-status', [QueueController::class, 'updateStatus'])
        ->name('queues.update-status');

    Route::delete('/queues/{queue}', [QueueController::class, 'destroy'])
        ->name('queues.destroy');

});

/*
|--------------------------------------------------------------------------
| Portal del Cliente (QR de Mesa)
|--------------------------------------------------------------------------
*/

Route::prefix('m/{identifier}')->group(function () {

    // Menú principal
    Route::get('/', [CustomerController::class, 'showMenu'])
        ->name('customer.menu');

    // Buscador de canciones
    Route::get('/cantar', [CustomerController::class, 'showSongSearch'])
        ->name('customer.songs');

    // API búsqueda
    Route::get('/search-api', [CustomerController::class, 'searchSongs'])
        ->name('customer.songs.search');

    // Guardar canción en cola
    Route::post('/request-song', [CustomerController::class, 'storeSong'])
        ->name('customer.songs.store');

});

/*
|--------------------------------------------------------------------------
| Stage / Pantalla de Proyección (TV)
|--------------------------------------------------------------------------
*/

Route::get('/stage/{user}', [StageController::class, 'show'])
    ->name('stage.show');

/* API para la TV (auto refresh) */
Route::get('/stage/{user}/current', [StageController::class, 'current'])
    ->name('stage.current');

/* finalizar canción desde la TV */
Route::post('/stage/{queue}/finish', [StageController::class, 'finish'])
    ->name('stage.finish');

/*
|--------------------------------------------------------------------------
| Perfil de Usuario
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});

require __DIR__ . '/auth.php';
