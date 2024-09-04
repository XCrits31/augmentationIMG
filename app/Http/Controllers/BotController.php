<?php

namespace App\Http\Controllers;

use App\Models\TgUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BotController extends Controller
{

    public function handleWebhook(Request $request)
    {
        return response()->json(['status' => 'success']);
    }

    protected function sendMessage($chatId, $message)
    {
        $url = "https://api.telegram.org/bot" . env('TG_API') . "/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
        ];

        Http::post($url, $data);
    }
}
