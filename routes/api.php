<?php

use App\Presentation\Http\Controllers\AuthController;
use App\Presentation\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout',   [AuthController::class,     'logout']);
    Route::post('/transfer', [TransactionController::class, 'transfer']);
});
