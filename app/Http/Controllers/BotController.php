<?php

namespace App\Http\Controllers;

use App\Models\TgUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{

    public function handleWebhook(Request $request)
    {
        $update = $request->all();
        $telegramId = $update['message']['from']['id'];
        $name = $update['message']['from']['first_name'];
        $username = $update['message']['from']['username'] ?? null;

        TgUser::updateOrCreate(
            ['telegram_id' => $telegramId],
            ['name' => $name, 'username' => $username]
        );

        $responseMessage = "Hello, {$name}";

        $this->sendMessage($telegramId, $responseMessage);
    }

    protected function sendMessage($chatId, $message)
    {
        $url = "https://api.telegram.org/bot" . env('TG_API') . "/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
        ];
        $responce = Http::post("https://api.trello.com/1/webhooks/?key=ddedd3e7a8eee4e2ef154e9a5933798d&token=ATTAf9a068bc0402f498b8f75461090a484ea1e01e602e57ad32a88994de78822deeE8A16C1C", [
            'callbackURL' => 'https://xcrits31.su/webhook-trello',
            'idModel' => '66d986833ad2d7b6caab0a61',
            'description' => 'Webhook for card move',
        ]);
        Log::info($responce);
        Http::post($url, $data);
    }
}
