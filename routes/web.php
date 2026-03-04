<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ServiceTableController;
use App\Http\Controllers\ShopSettingsController;
use App\Http\Controllers\SongController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settings', [ShopSettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [ShopSettingsController::class, 'update'])->name('settings.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/songs', [SongController::class, 'index'])->name('songs.index');
    Route::post('/songs', [SongController::class, 'store'])->name('songs.store');
    Route::delete('/songs/{song}', [SongController::class, 'destroy'])->name('songs.destroy');
});

Route::middleware(['auth'])->group(function () {
    // Gestión de Mesas
    Route::get('/tables', [ServiceTableController::class, 'index'])->name('tables.index');
    Route::post('/tables', [ServiceTableController::class, 'store'])->name('tables.store');
    Route::put('/tables/{table}', [ServiceTableController::class, 'update'])->name('tables.update');
    Route::delete('/tables/{table}', [ServiceTableController::class, 'destroy'])->name('tables.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/queues', [QueueController::class, 'index'])->name('queues.index');
    Route::patch('/queues/{queue}/status', [QueueController::class, 'updateStatus'])->name('queues.update-status');
});

// --- RUTAS DEL PORTAL DEL CLIENTE ---
// Usamos un prefijo para que todas las URLs empiecen con /m/{mesa}
Route::prefix('m/{identifier}')->group(function () {

    // 1. El Menú principal (Selección: Cantar, Comer, Beber)
    Route::get('/', [CustomerController::class, 'showMenu'])
        ->name('customer.menu');

    // 2. La Vista del Buscador de Karaoke
    Route::get('/cantar', [CustomerController::class, 'showSongSearch'])
        ->name('customer.songs');

    // 3. API Interna para la búsqueda (La que usa el Debounce)
    Route::get('/search-api', [CustomerController::class, 'searchSongs'])
        ->name('customer.songs.search');

    // 4. Acción de guardar la petición en la cola
    Route::post('/request-song', [CustomerController::class, 'storeSong'])
        ->name('customer.songs.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
