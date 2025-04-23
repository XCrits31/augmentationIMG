<?php

namespace App\Listeners;

use App\Events\ProcessImageCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyProcessedImage implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ProcessImageCompleted $event)
    {

        \Log::info('processed succesfully/Notify');

    }
}

