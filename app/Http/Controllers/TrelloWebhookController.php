<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrelloWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Trello Webhook Received:', $request->all());

        return response('Webhook handled', 200);
    }

    private function sendToTelegram($card, $listBefore, $listAfter)
    {
        $telegramBotToken = env('TELEGRAM_BOT_TOKEN');
        $telegramChatId = env('TELEGRAM_CHAT_ID');
        $message = "Card '{$card['name']}' has been moved from '{$listBefore}' to '{$listAfter}'.";

        $url = "https://api.telegram.org/bot$telegramBotToken/sendMessage";

        Http::post($url, [
            'chat_id' => $telegramChatId,
            'text' => $message
        ]);
    }
}
