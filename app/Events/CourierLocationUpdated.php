<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourierLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $latitude;
    public $longitude;

    public function __construct(Order $order, $latitude, $longitude)
    {
        $this->order     = $order;
        $this->latitude  = $latitude;
        $this->longitude = $longitude;
    }

    public function broadcastOn(): array
    {
        return [new Channel('order.' . $this->order->id)];
    }

    public function broadcastAs(): string
    {
        return 'CourierLocationUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}