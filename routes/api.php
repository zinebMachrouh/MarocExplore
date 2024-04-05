<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RouteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::controller(AuthController::class)->group(function () {
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::post('login', 'login');
    Route::post('register', 'register');

});

Route::get('/routes',[RouteController::class,'index']);
Route::post('/routes/create', [RouteController::class, 'store']);
Route::put('/routes/{route}/update', [RouteController::class, 'update']);
Route::delete('/routes/{route}/delete', [RouteController::class, 'destroy']);
Route::post('/routes/search', [RouteController::class, 'search']);
Route::post('/routes/filter', [RouteController::class, 'filter']);

Route::get('/addToWatchlist/{route}', [RouteController::class, 'addToWatchlist']);
Route::post('/destinations/create', [RouteController::class, 'createDistination']);
