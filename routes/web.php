<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\GeneratorController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Package;

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

Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');


Route::middleware(['auth'])->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/services', [ServicesController::class, 'edit'])->name('service.edit');

    Route::post('/services', [ServicesController::class, 'store'])->name('service.store');
    Route::delete('/services/{id}', [ServicesController::class, 'destroy'])->name('service.destroy');

    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::delete('/packages/{id}', [PackageController::class, 'destroy'])->name('packages.destroy');
    Route::get('/packages/create', [PackageController::class, 'create'])->name('packages.create');
    Route::post('/packages/store', [PackageController::class, 'store'])->name('packages.store');
    Route::get('/packages/test', [PackageController::class, 'test'])->name('packages.test');
    Route::post('/packages/test', [PackageController::class, 'verify'])->name('packages.verify');

});


Route::get('/generator', [GeneratorController::class, 'index'])->name('generator.index');


Route::get('/generator/{packageName}/generate/{id}', [GeneratorController::class, 'create'])->name('generator.create')->where('packageName', '.*');
Route::post('/generator/{packageName}/generate/{id}', [GeneratorController::class, 'generate'])->name('generator.generate')->where('packageName', '.*');
Route::get('/generator/{packageName}', [GeneratorController::class, 'show'])->name('generator.show')->where('packageName', '.*');

Route::get('/error/404', function () {
  abort(404);
});


require __DIR__.'/auth.php';
