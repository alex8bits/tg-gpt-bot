<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;

Route::get('webhook', [TelegramBotController::class, 'setWebhook']);
Route::get('webhook/unset', [TelegramBotController::class, 'unsetWebhook']);
Route::get('/', function () {
    return view('welcome');
});
