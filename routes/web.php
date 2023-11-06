<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\GeneratorController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
  return redirect()->route('dashboard');
})->name('index');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/services', [ServicesController::class, 'edit'])->name('service.edit');

    Route::post('/services', [ServicesController::class, 'store'])->name('service.store');
    Route::delete('/services/{id}', [ServicesController::class, 'destroy'])->name('service.destroy');

    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/create', [PackageController::class, 'create'])->name('packages.create');
    Route::post('/packages/store', [PackageController::class, 'store'])->name('packages.store');
    Route::get('/packages/import', [PackageController::class, 'importCreate'])->name('packages.import.create');
    Route::post('/packages/import', [PackageController::class, 'importStore'])->name('packages.import.store');
});


Route::get('/generator', [GeneratorController::class, 'index'])->name('generator.index');


Route::get('/generator/{vendor}/{package}', [GeneratorController::class, 'show'])->name('generator.show');
Route::get('/generator/{vendor}/{package}/generate/{id}', [GeneratorController::class, 'create'])->name('generator.create');
Route::post('/generator/{vendor}/{package}/generate/{id}', [GeneratorController::class, 'generate'])->name('generator.generate');


require __DIR__.'/auth.php';
