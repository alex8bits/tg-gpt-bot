<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::post('botwebhook/{token}', [TelegramBotController::class, 'handleWebhook']);
});
