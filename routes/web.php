<?php

use App\Http\Controllers\BotController;
use App\Http\Controllers\TrelloWebhookController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhook', [BotController::class, 'handleWebhook']);

//Route::post('/webhook-trello', [TrelloWebhookController::class, 'handle']);

Route::post('/webhook-trello', function (Request $request) {
    LOG::info('Получен Webhook от Trello', $request->all());

    return response('OK', 200);
});
