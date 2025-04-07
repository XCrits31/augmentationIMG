<?php

use App\Http\Controllers\BotController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', [BotController::class, 'index']);
