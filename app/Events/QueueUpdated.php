<?php
namespace App\Events;

use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Queue $queue;
    public array $fullQueue;

    /**
     * Create a new event instance.
     */
    public function __construct(Queue $queue, array $fullQueue)
    {
        $this->queue     = $queue;
        $this->fullQueue = $fullQueue;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('karaoke.' . $this->queue->user_id);
    }

    /**
     * Event name for frontend listener
     */
    public function broadcastAs(): string
    {
        return 'queue.updated';
    }
}
