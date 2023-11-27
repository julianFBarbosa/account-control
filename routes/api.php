<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\UserController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(UserController::class)->group(function () {
    Route::get('/users', 'index');
    Route::post('/users/create', 'store');
    Route::put('/users/update/{id}', 'update');
    Route::delete('/users/delete/{id}', 'destroy');
});

Route::controller(AccountController::class)->group(function () {
    Route::get('/accounts/{email}', 'show');
    Route::post('/accounts/create/{email}', 'store');
    Route::put('/accounts/upate/{email}', 'update');
});
