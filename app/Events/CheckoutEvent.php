<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckoutEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $name;
    public $message;

    public function __construct($name , $message)
    {
        $this->name = $name;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return ['OrdersChannel'];
    }

    public function broadcastAs()
    {
        return 'checkout-done';
    }
}
