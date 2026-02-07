<?php

namespace HardImpact\OpenCode\Requests\Sessions;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListSessions extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/session';
    }
}
