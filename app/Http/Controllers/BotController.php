<?php

namespace App\Http\Controllers;

use App\Models\TgUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

        Http::post($url, $data);
    }
}
