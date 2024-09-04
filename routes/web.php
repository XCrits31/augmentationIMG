<?php

use App\Http\Controllers\BotController;
use App\Http\Controllers\TrelloWebhookController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhook', [BotController::class, 'handleWebhook']);

Route::post('/webhook/trello', [TrelloWebhookController::class, 'handle']);
