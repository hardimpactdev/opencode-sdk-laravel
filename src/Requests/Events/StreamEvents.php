<?php

namespace HardImpact\OpenCode\Requests\Events;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class StreamEvents extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/event';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'stream' => true,
            'timeout' => 0,
        ];
    }
}
