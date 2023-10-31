<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderMaking
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return ['webina'];
    }

    public function broadcastAs()
    {
        return 'order_making';
    }

    public function broadcastWith()
    {
        return [
            'message' => 'New order created!',
            'user_name' => $this->message,
            // Include other order data if needed
        ];
    }
}
