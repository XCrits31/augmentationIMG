<?php

namespace App\Http\Controllers;

use Illuminate\Console\Command;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrelloWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Trello Webhook Received:', $request->all());


        return response('Webhook handled', 200);
    }
}
