<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentFlowController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\PaperController;
use App\Http\Controllers\PaperDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ScreenController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\WindowController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/time', function () {
    return [
        'app_time' => now()->toDateTimeString(),
        'server_time' => date('Y-m-d H:i:s'),
    ];
});

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/test', function () {
    return Inertia::render('Welcomev2', []);
});

Route::middleware('auth')->group(function () {
    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Queue management routes
    Route::name('queue.')->group(function () {
        Route::get('/', [QueueController::class, 'index'])->name('index');
        Route::get('/create', [QueueController::class, 'create'])->name('create');
        Route::post('/store', [QueueController::class, 'store'])->name('store');
        Route::post('/{queueItem}/call', [QueueController::class, 'call'])->name('call');
        Route::post('/{queueItem}/complete', [QueueController::class, 'complete'])->name('complete');
        Route::post('/{queueItem}/complete-and-transfer', [QueueController::class, 'completeAndTransfer'])->name('complete-and-transfer');
        Route::post('/{queueItem}/no-show', [QueueController::class, 'noShow'])->name('no-show');
        Route::post('/{queueItem}/transfer', [QueueController::class, 'transfer'])->name('transfer');
        Route::post('/{queueItem}/transfer', [QueueController::class, 'skip'])->name('skip');
        Route::get('/department/{departmentId}', [QueueController::class, 'departmentQueue'])->name('department');
        Route::post('/department/{departmentId}/reset-counter', [QueueController::class, 'resetCounter'])
            ->name('reset-counter')
            ->middleware('admin');
        Route::get('/{departmentId}/data', [QueueController::class, 'departmentQueueData'])
            ->name('departmentQueueData');
    });

    // Department management routes
    Route::resource('departments', DepartmentController::class);

    // Department flow routes
    Route::resource('department-flows', DepartmentFlowController::class)->parameters([
        'department-flows' => 'department_flow'
    ]);

    // Screens
    Route::prefix('screens')->name('windows.')->group(function () {
        Route::get('/', [WindowController::class, 'index'])->name('index');
        Route::get('/{window}', [WindowController::class, 'show'])->name('show');
        Route::get('/{window}/data', [WindowController::class, 'data'])->name('data');
        Route::get('/{window}/edit', [WindowController::class, 'edit'])->name('edit')->middleware('admin');
        Route::put('/{window}', [WindowController::class, 'update'])->name('update')->middleware('admin');
    });
});


// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

//     Route::get('/papers/data', [PaperDashboardController::class, 'getData'])->name('papers.data');
//     Route::get('/offices/data', [PaperDashboardController::class, 'getOfficeData'])->name('offices.data');

//     Route::get('/papers/dashboard', [PaperDashboardController::class, 'index'])->name('papers.dashboard');
//     Route::resource('papers', PaperController::class);
//     Route::resource('offices', OfficeController::class)->except(['destroy']);
//     Route::resource('tags', TagController::class)->except(['destroy']);
// });

Route::post('/theme/update', [ThemeController::class, 'update'])->name('theme.update');

require __DIR__ . '/auth.php';
