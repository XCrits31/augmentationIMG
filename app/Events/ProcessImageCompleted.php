<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProcessImageCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $image_path;
    public $message;

    public function __construct(string $image_path, string $message)
    {
        $this->image_path = $image_path;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     */
    public function broadcastOn()
    {
        return ['image-processing'];
    }

    public function broadcastAs()
    {
        return 'batch.completed';
    }
    public function broadcastWith()
    {
        return [
            'image_path' => $this->image_path,
            'message' => $this->message,
        ];
    }
}
