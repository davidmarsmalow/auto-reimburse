<?php

use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('pdf');
});

Route::group(['prefix' => '/export'], function () {
    Route::get('/', [ExportController::class, 'index']);
    Route::post('/', [ExportController::class, 'generate']);
    Route::post('/pdf', [ExportController::class, 'pdf']);
    Route::post('/docx', [ExportController::class, 'docx']);
});
