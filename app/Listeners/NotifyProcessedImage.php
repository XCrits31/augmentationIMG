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
        // Данные, переданные из события
        $data = $event->data;

        // Логика обработки события, например, отправка уведомления
        \Log::info('Process completed for image:', $data);

        // Или отправка сообщения, например, пользователю
    }
}

