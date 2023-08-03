<?php

namespace App\Events;

use App\Models\Examine;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamineStartEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public int $examine;
    public int $seconds;
    /**
     * Create a new event instance.
     */
    public function __construct(int $examine,$seconds=0)
    {
        $this->examine=$examine;
        $this->seconds=$seconds;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
