<?php

namespace App\Http\Controllers;

use App\Models\TgUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class BotController extends Controller
{

    public function index()
    {
        $scriptPath = base_path('scripts/testscript.py');
        $command = escapeshellcmd("python3 {$scriptPath}");
        $output = shell_exec($command);

        return view('welcome', ['pythonOutput' => $output]);
    }
}
