<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;
use Telegram\Bot\Laravel\Facades\Telegram;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
//Route::middleware('auth:api')->get("/bot",[BotController::class,"getMe"]);
Route::get('setUpWebHook',[BotController::class,'setUpWebHook'])->name("bot.setUpWebHook");
Route::middleware(['botRequestCheck','client'])->post('',function (){
    $update=Telegram::commandsHandler(true);
    event(new \App\Events\BotMessageEvent($update));
});
Route::get('removeWebhook',[BotController::class,'removeWebhook']);
Route::get('getMe',[BotController::class,'getMe']);
Route::get('getUpdates',[BotController::class,'getUpdates']);
Route::middleware('botRequestCheck','client')->get('test',[BotController::class,'test']);
