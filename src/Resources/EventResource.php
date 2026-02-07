<?php

namespace HardImpact\OpenCode\Resources;

use Generator;
use HardImpact\OpenCode\Data\Event;
use HardImpact\OpenCode\Requests\Events\StreamEvents;
use HardImpact\OpenCode\Support\EventStream;
use Saloon\Http\BaseResource;

class EventResource extends BaseResource
{
    /** @return Generator<Event> */
    public function stream(): Generator
    {
        $response = $this->connector->send(new StreamEvents);
        $eventStream = new EventStream($response->stream());

        return $eventStream->events();
    }
}
