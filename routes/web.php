<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;

Route::get('webhook', [TelegramBotController::class, 'setWebhook']);
Route::get('webhook/unset', [TelegramBotController::class, 'unsetWebhook']);
Route::get('webhook/info', [TelegramBotController::class, 'getInfo']);
Route::post('webhook/{token}', [TelegramBotController::class, 'handleWebhook']);
Route::get('/', function () {
    return view('welcome');
});
