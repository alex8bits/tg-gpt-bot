<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;

Route::get('webhook', [TelegramBotController::class, 'setWebhook']);
Route::get('/', function () {
    return view('welcome');
});
