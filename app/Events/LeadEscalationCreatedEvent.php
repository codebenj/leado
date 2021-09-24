<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\LeadEscalation;

class LeadEscalationCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $lead_escalation;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(LeadEscalation $lead_escalation)
    {
        $this->lead_escalation = $lead_escalation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('traleado-global');
    }

    public function broadcastAs()
    {
        return 'leadescalation.created';
    }
}
