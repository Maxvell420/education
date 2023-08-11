<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;
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
//Route::middleware('auth:api')->get("/bot",[BotController::class,"getMe"]);
Route::get("/NgrokUri",[BotController::class,"getNgrokUri"]);
Route::get("/test",[BotController::class,"test"]);
Route::post('setUpNgrok',[BotController::class,'setUpNgrok'])->name("setNgrokToBot");
