<?php

use App\Http\Controllers\BotController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  //  $http = Http::get("https://api.telegram.org/bot" . env('TG_API') . "/getWebhookInfo");
 //   dd(json_decode($http));
    return view('welcome');
});
