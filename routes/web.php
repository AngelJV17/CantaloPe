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
| Página Pública
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

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', fn() =>
        Inertia::render('Dashboard')
    )->name('dashboard');

});

/*
|--------------------------------------------------------------------------
| Configuración del Local
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->prefix('settings')->group(function () {

    Route::get('/', [ShopSettingsController::class, 'edit'])
        ->name('settings.edit');

    Route::patch('/', [ShopSettingsController::class, 'update'])
        ->name('settings.update');

});

/*
|--------------------------------------------------------------------------
| Canciones
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('songs')->group(function () {

    Route::get('/', [SongController::class, 'index'])
        ->name('songs.index');

    Route::post('/', [SongController::class, 'store'])
        ->name('songs.store');

    Route::delete('/{song}', [SongController::class, 'destroy'])
        ->name('songs.destroy');

});

/*
|--------------------------------------------------------------------------
| Mesas
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('tables')->group(function () {

    Route::get('/', [ServiceTableController::class, 'index'])
        ->name('tables.index');

    Route::post('/', [ServiceTableController::class, 'store'])
        ->name('tables.store');

    Route::put('/{table}', [ServiceTableController::class, 'update'])
        ->name('tables.update');

    Route::delete('/{table}', [ServiceTableController::class, 'destroy'])
        ->name('tables.destroy');

});

/*
|--------------------------------------------------------------------------
| Cola de Karaoke
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/queues', [QueueController::class, 'index'])
        ->name('queues.index');

    Route::post('/queues', [QueueController::class, 'store'])
        ->name('queues.store');

    Route::patch('/queues/{queue}/status', [QueueController::class, 'updateStatus'])
        ->name('queues.update-status');
});

/*
|--------------------------------------------------------------------------
| API INTERNA (REALTIME)
|--------------------------------------------------------------------------
*/

Route::prefix('api')->group(function () {

    // Obtener cola en tiempo real
    Route::get('/queues/{user}', [QueueController::class, 'apiQueues'])
        ->name('api.queues');

});

/*
|--------------------------------------------------------------------------
| Portal Cliente (QR Mesa)
|--------------------------------------------------------------------------
*/

Route::prefix('m/{identifier}')->group(function () {

    Route::get('/', [CustomerController::class, 'showMenu'])
        ->name('customer.menu');

    Route::get('/cantar', [CustomerController::class, 'showSongSearch'])
        ->name('customer.songs');

    Route::get('/search-api', [CustomerController::class, 'searchSongs'])
        ->name('customer.songs.search');

    Route::post('/request-song', [CustomerController::class, 'storeSong'])
        ->name('customer.songs.store');

});

/*
|--------------------------------------------------------------------------
| Pantalla Stage (TV)
|--------------------------------------------------------------------------
*/

Route::prefix('stage')->group(function () {

    // Vista del escenario
    Route::get('/{user}', [StageController::class, 'show'])
        ->name('stage.show');

    // API canción actual
    Route::get('/{user}/current', [StageController::class, 'current'])
        ->name('stage.current');

    // finalizar canción
    Route::post('/{queue}/finish', [StageController::class, 'finish'])
        ->name('stage.finish');

});

/*
|--------------------------------------------------------------------------
| Perfil
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
