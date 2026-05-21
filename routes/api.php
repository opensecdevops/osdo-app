<?php

use App\Http\Controllers\Cli\CliAuthController;
use App\Http\Controllers\Cli\CliDeploymentController;
use App\Http\Controllers\Cli\CliPackageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — OSDO CLI Integration
|--------------------------------------------------------------------------
|
| Rutas para la integración del CLI con la OSDO App.
| La autenticación se realiza mediante Personal Access Tokens de Sanctum.
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Autenticación del CLI — Emite un Sanctum Personal Access Token
Route::post('/cli/auth', [CliAuthController::class, 'token'])
    ->name('cli.auth.token');

// Revocar token CLI
Route::delete('/cli/auth', [CliAuthController::class, 'logout'])
    ->middleware('auth:sanctum')
    ->name('cli.auth.logout');

// Rutas protegidas del CLI — Requieren Bearer Token de Sanctum
Route::prefix('cli')->middleware('auth:sanctum')->group(function () {

    // Paquetes: listar, detallar y descargar
    Route::get('/packages', [CliPackageController::class, 'index'])
        ->name('cli.packages.index');
    Route::get('/packages/{id}', [CliPackageController::class, 'show'])
        ->name('cli.packages.show');
    Route::get('/packages/{id}/download', [CliPackageController::class, 'download'])
        ->name('cli.packages.download');

    // Deployments: registrar, actualizar estado y listar
    Route::post('/deployments', [CliDeploymentController::class, 'store'])
        ->name('cli.deployments.store');
    Route::patch('/deployments/{id}/status', [CliDeploymentController::class, 'updateStatus'])
        ->name('cli.deployments.update');
    Route::get('/deployments', [CliDeploymentController::class, 'index'])
        ->name('cli.deployments.index');
});
