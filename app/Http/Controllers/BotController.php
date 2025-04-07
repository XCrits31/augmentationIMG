<?php

namespace App\Http\Controllers;

use App\Models\TgUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class BotController extends Controller
{

    public function handleWebhook(Request $request)
    {
        $argument = 'test';
        $command = escapeshellcmd("python3 scripts/testscript.py {$argument}");
        $output = shell_exec($command);

        echo $output;
    }
}
