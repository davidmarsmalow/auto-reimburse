<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\ExportController;
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

Route::resource('bills', BillController::class);

Route::group(['prefix' => '/export'], function () {
    Route::post('/pdf', [ExportController::class, 'pdf']);
    Route::post('/docx', [ExportController::class, 'docx']);
});