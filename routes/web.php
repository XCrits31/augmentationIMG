<?php

use App\Http\Controllers\BotController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $http = Http::get("https://api.telegram.org/bot" . env('TG_API') . "/setWebhook?url=https://xcrits31.su/webhook");
    dd(json_decode($http));
    //return view('welcome');
});

Route::post('/webhook', [BotController::class, 'handleWebhook']);
