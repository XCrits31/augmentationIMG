<?php

namespace App\Http\Controllers;

use Illuminate\Console\Command;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class TrelloWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Trello Webhook Received:', $request->all());
        $url = "https://api.telegram.org/bot" . env('TG_API') . "/sendMessage";
        $data = [
            'chat_id' => '1050201265',
            'text' => 'trello',
        ];
        Http::post($url, $data);
        return response()->json(['status' => 'success']);

    }
}
